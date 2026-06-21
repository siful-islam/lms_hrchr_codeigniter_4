<?php

namespace App\Controllers\Hrchr;

require_once APPPATH . 'libraries/HrchrServiceAuth.php';
require_once APPPATH . 'libraries/HrchrEnrollmentService.php';

/**
 * LMS Phase 3B: idempotent enrollment write endpoint for the
 * hrchr-core ↔ hrchr-lms integration.
 *
 *   POST /api/hrchr/enrollments    (routed by application/config/routes.php)
 *
 * Auth:
 *   - Same shared-secret model as Phase 2A `Courseapi` reads --
 *     `HrchrServiceAuth` reads env `LMS_SERVICE_API_SECRET` and
 *     accepts either `Authorization: Bearer <secret>` or the
 *     `X-HRCHR-Service-Secret: <secret>` header. Fails closed when
 *     the env is unset. Constant-time compare; the secret is never
 *     logged.
 *
 * Idempotency:
 *   - The legacy LMS `enrol` table carries no UNIQUE constraint
 *     and already contains duplicate (user_id, course_id) rows from
 *     the upstream Academy LMS path (gifts / re-purchases).
 *   - Our dedup invariant therefore lives in
 *     `hrchr_lms_enrollment_links` (Phase 3B install file):
 *     `UNIQUE idempotency_key`, `UNIQUE assignment_target_id`. A
 *     repeat call with either match collapses to `status=exists`
 *     and returns the existing `lms_enrollment_id`.
 *
 * Logging policy:
 *   - Only safe IDs and reason enums are logged.
 *   - The request body (which includes an email) is NEVER logged.
 *   - The `LMS_SERVICE_API_SECRET` value is NEVER logged.
 *
 * Response shape:
 *   - JSON only; `Cache-Control: no-store`; never echoes the
 *     secret or the verbatim email.
 *   - Success envelope:
 *       { ok: true, status: "created"|"exists",
 *         assignment_id, assignment_target_id, lms_course_id,
 *         lms_user_id, lms_enrollment_id, idempotency_key }
 *   - Failure envelope:
 *       { ok: false, status: <enum>, [retryable: bool] }
 *
 * What this controller does NOT do (deferred by Phase 3 design):
 *   - JIT-create an LMS user. Provisioning is owned by
 *     `Sso::callback` so the claims-based identity flow stays the
 *     single source of truth.
 *   - Touch `payment` / `team_package_payment`. Paid courses are
 *     refused in MVP (`paid_course_not_supported`).
 *   - Add UNIQUE / NOT NULL constraints to the legacy `enrol`
 *     table. Idempotency is enforced in the link table only.
 *   - Push completion / progress back to core -- that's Phase 3F.
 */
use App\Controllers\BaseController;

class Enrollments extends BaseController
{
    /** @var HrchrServiceAuth */
    private $serviceAuth;

    /** @var HrchrEnrollmentService */
    private $service;

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->library('session');

        $this->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate')
            ->set_header('Pragma: no-cache')
            ->set_content_type('application/json');

        $this->serviceAuth = new HrchrServiceAuth();
        $this->service     = new HrchrEnrollmentService($this->db);
    }

    /**
     * POST /api/hrchr/enrollments
     *
     * Auth precedes payload validation precedes user resolution
     * precedes course validation precedes idempotency check
     * precedes enrol+link write.
     */
    public function create()
    {
        if (strtoupper((string) $this->input->server('REQUEST_METHOD')) !== 'POST') {
            return $this->reply(405, array(
                'ok'     => false,
                'status' => 'method_not_allowed',
            ));
        }

        if (!$this->enforceServiceAuth()) {
            return;
        }

        $raw = (string) $this->input->raw_input_stream;
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return $this->reply(422, array(
                'ok'     => false,
                'status' => HrchrEnrollmentService::STATUS_INVALID_PAYLOAD,
            ));
        }

        $normalised = $this->service->validatePayload($payload);
        if (empty($normalised['ok'])) {
            return $this->reply(422, array(
                'ok'     => false,
                'status' => isset($normalised['reason'])
                    ? $normalised['reason']
                    : HrchrEnrollmentService::STATUS_INVALID_PAYLOAD,
            ));
        }

        // Idempotency short-circuit: existing link → exists.
        $existingLink = $this->service->findExistingLink(
            $normalised['assignment_target_id'],
            $normalised['idempotency_key']
        );
        if ($existingLink !== null) {
            log_message('info', sprintf(
                'HRCHR enrollment idempotent hit: assignment_id=%d target_id=%d link_id=%d',
                $normalised['assignment_id'],
                $normalised['assignment_target_id'],
                (int) $existingLink['id']
            ));
            return $this->reply(200, array(
                'ok'                   => true,
                'status'               => HrchrEnrollmentService::STATUS_EXISTS,
                'assignment_id'        => $normalised['assignment_id'],
                'assignment_target_id' => $normalised['assignment_target_id'],
                'lms_course_id'        => (int) $existingLink['lms_course_id'],
                'lms_user_id'          => (int) $existingLink['lms_user_id'],
                'lms_enrollment_id'    => isset($existingLink['lms_enrol_id']) ? (int) $existingLink['lms_enrol_id'] : null,
                'idempotency_key'      => (string) $existingLink['idempotency_key'],
            ));
        }

        // Resolve LMS user. NEVER JIT-create here.
        $lmsUserId = $this->service->resolveLmsUserId(
            $normalised['core_user_id'],
            $normalised['email']
        );
        if ($lmsUserId === null) {
            log_message('info', sprintf(
                'HRCHR enrollment user_not_provisioned: assignment_id=%d target_id=%d',
                $normalised['assignment_id'],
                $normalised['assignment_target_id']
            ));
            return $this->reply(202, array(
                'ok'        => false,
                'status'    => HrchrEnrollmentService::STATUS_USER_NOT_PROVISIONED,
                'retryable' => true,
            ));
        }

        // Course exists + free check.
        $course = $this->service->inspectCourse($normalised['lms_course_id']);
        if ($course === null) {
            return $this->reply(404, array(
                'ok'     => false,
                'status' => HrchrEnrollmentService::STATUS_COURSE_NOT_FOUND,
            ));
        }
        if (empty($course['is_free'])) {
            return $this->reply(409, array(
                'ok'        => false,
                'status'    => HrchrEnrollmentService::STATUS_PAID_NOT_SUPPORTED,
                'retryable' => false,
            ));
        }

        // Reuse an existing legacy `enrol` row if one exists.
        $existingEnrol = $this->service->findExistingEnrol($lmsUserId, $course['id']);
        $enrolId = $existingEnrol !== null && !empty($existingEnrol['id'])
            ? (int) $existingEnrol['id']
            : null;

        $createdNewEnrol = false;
        if ($enrolId === null) {
            $enrolId = $this->service->createEnrol(
                $lmsUserId,
                $course['id'],
                $normalised['due_date']
            );
            $createdNewEnrol = ($enrolId !== null);

            if ($enrolId === null) {
                log_message('error', sprintf(
                    'HRCHR enrollment enrol insert failed: assignment_id=%d target_id=%d',
                    $normalised['assignment_id'],
                    $normalised['assignment_target_id']
                ));
                return $this->reply(500, array(
                    'ok'     => false,
                    'status' => HrchrEnrollmentService::STATUS_ENROLLMENT_FAILED,
                ));
            }
        }

        $linkStatus = $createdNewEnrol
            ? HrchrEnrollmentService::STATUS_CREATED
            : HrchrEnrollmentService::STATUS_EXISTS;

        $responseEnvelope = array(
            'ok'                   => true,
            'status'               => $linkStatus,
            'assignment_id'        => $normalised['assignment_id'],
            'assignment_target_id' => $normalised['assignment_target_id'],
            'lms_course_id'        => $course['id'],
            'lms_user_id'          => $lmsUserId,
            'lms_enrollment_id'    => $enrolId,
            'idempotency_key'      => $normalised['idempotency_key'],
        );

        $linkId = $this->service->createLink(array(
            'assignment_id'        => $normalised['assignment_id'],
            'assignment_target_id' => $normalised['assignment_target_id'],
            'idempotency_key'      => $normalised['idempotency_key'],
            'core_user_id'         => $normalised['core_user_id'] !== '' ? $normalised['core_user_id'] : null,
            'lms_user_id'          => $lmsUserId,
            'lms_course_id'        => $course['id'],
            'lms_enrol_id'         => $enrolId,
            'status'               => $linkStatus,
            'tenant_id'            => $normalised['tenant_id'],
            'expiry_date'          => $normalised['due_date'],
            'response_json'        => json_encode($responseEnvelope),
        ));

        if ($linkId === null) {
            // The most likely cause is a race: a concurrent request
            // beat us to the link row (UNIQUE collision on
            // idempotency_key or assignment_target_id). Re-read and
            // return that row instead of an error.
            $existingLink = $this->service->findExistingLink(
                $normalised['assignment_target_id'],
                $normalised['idempotency_key']
            );
            if ($existingLink !== null) {
                return $this->reply(200, array(
                    'ok'                   => true,
                    'status'               => HrchrEnrollmentService::STATUS_EXISTS,
                    'assignment_id'        => $normalised['assignment_id'],
                    'assignment_target_id' => $normalised['assignment_target_id'],
                    'lms_course_id'        => (int) $existingLink['lms_course_id'],
                    'lms_user_id'          => (int) $existingLink['lms_user_id'],
                    'lms_enrollment_id'    => isset($existingLink['lms_enrol_id']) ? (int) $existingLink['lms_enrol_id'] : null,
                    'idempotency_key'      => (string) $existingLink['idempotency_key'],
                ));
            }

            log_message('error', sprintf(
                'HRCHR enrollment link insert failed: assignment_id=%d target_id=%d enrol_id=%d',
                $normalised['assignment_id'],
                $normalised['assignment_target_id'],
                $enrolId
            ));
            return $this->reply(500, array(
                'ok'     => false,
                'status' => HrchrEnrollmentService::STATUS_ENROLLMENT_FAILED,
            ));
        }

        log_message('info', sprintf(
            'HRCHR enrollment %s: assignment_id=%d target_id=%d link_id=%d enrol_id=%d',
            $linkStatus,
            $normalised['assignment_id'],
            $normalised['assignment_target_id'],
            $linkId,
            $enrolId
        ));

        return $this->reply(($linkStatus === HrchrEnrollmentService::STATUS_CREATED) ? 201 : 200, $responseEnvelope);
    }

    /**
     * Verify the request carries a valid service credential. On
     * failure, writes the appropriate JSON error envelope + status
     * code and returns false so the caller can `return;` immediately.
     * Mirrors Courseapi::enforceServiceAuth.
     *
     * @return bool true if the request is authorised, false otherwise.
     */
    private function enforceServiceAuth()
    {
        $result = $this->serviceAuth->verifyRequest();
        if (!empty($result['ok'])) {
            return true;
        }

        $reason = isset($result['reason']) ? $result['reason'] : HrchrServiceAuth::REASON_INVALID;
        $status = $reason === HrchrServiceAuth::REASON_NOT_CONFIGURED ? 503 : 401;

        log_message('error', 'HRCHR enrollment API service-auth refused: ' . $reason);

        $this->reply($status, array(
            'ok'     => false,
            'status' => $reason,
        ));

        return false;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function reply($status, array $body)
    {
        $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json')
            ->set_output(json_encode($body, JSON_UNESCAPED_SLASHES));
    }
}


