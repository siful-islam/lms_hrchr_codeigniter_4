<?php

namespace App\Controllers\Hrchr;

require_once APPPATH . 'libraries/HrchrServiceAuth.php';
require_once APPPATH . 'libraries/HrchrProgressService.php';

/**
 * LMS Phase 3H-2: read-only progress / certificate API for the
 * hrchr-core <-> hrchr-lms progress sync.
 *
 *   GET /api/hrchr/progress    (routed by application/config/routes.php)
 *
 * Auth:
 *   - Same shared-secret model as Phase 3B `Enrollments` / Phase 2A
 *     `Courseapi` -- `HrchrServiceAuth` reads env `LMS_SERVICE_API_SECRET`
 *     and accepts `Authorization: Bearer <secret>` or
 *     `X-HRCHR-Service-Secret: <secret>`. Fails closed when the env is
 *     unset. Constant-time compare; the secret is never logged.
 *
 * Behaviour:
 *   - Strictly READ-ONLY -- no writes to any LMS table.
 *   - Recomputes progress from `watch_histories.completed_lesson`
 *     intersected with the course `lesson` rows (the cached
 *     `course_progress` column is not trusted).
 *   - At least one scoping filter (lms_user_id / lms_course_id /
 *     lms_enrollment_id / since) is required; there is no unbounded
 *     "return everything" default.
 *
 * Privacy:
 *   - JSON only; `Cache-Control: no-store`.
 *   - The response carries IDs + status enums only -- NEVER an email,
 *     a name, payment data, or the public certificate URL.
 *     `certificate_key` is the scrubbed `shareable_url` identifier.
 *   - The `LMS_SERVICE_API_SECRET` value is never logged.
 *
 * Response envelope:
 *   { ok:true, items:[ {lms_user_id, lms_course_id, lms_enrollment_id,
 *     progress_percent, completed_lessons, total_lessons,
 *     last_activity_at, completed_at, certificate_status,
 *     certificate_key, source_updated_at} ], limit, offset, count }
 *   Failure: { ok:false, status:<enum> }
 */
use App\Controllers\BaseController;

class Progress extends BaseController
{
    /** @var HrchrServiceAuth */
    private $serviceAuth;

    /** @var HrchrProgressService */
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
        $this->service     = new HrchrProgressService($this->db);
    }

    /**
     * GET /api/hrchr/progress
     *
     * Auth precedes filter validation precedes the read query.
     */
    public function index()
    {
        if (strtoupper((string) $this->input->server('REQUEST_METHOD')) !== 'GET') {
            return $this->reply(405, array(
                'ok'     => false,
                'status' => 'method_not_allowed',
            ));
        }

        if (!$this->enforceServiceAuth()) {
            return;
        }

        $filters = $this->service->normaliseFilters(array(
            'lms_user_id'       => $this->request->getGet('lms_user_id'),
            'lms_course_id'     => $this->request->getGet('lms_course_id'),
            'lms_enrollment_id' => $this->request->getGet('lms_enrollment_id'),
            'since'             => $this->request->getGet('since'),
            'limit'             => $this->request->getGet('limit'),
            'offset'            => $this->request->getGet('offset'),
        ));
        if (empty($filters['ok'])) {
            return $this->reply(422, array(
                'ok'     => false,
                'status' => isset($filters['reason'])
                    ? $filters['reason']
                    : HrchrProgressService::STATUS_INVALID_PAYLOAD,
            ));
        }

        $result = $this->service->query($filters);
        if (empty($result['ok'])) {
            log_message('error', 'HRCHR progress API query failed');
            return $this->reply(500, array(
                'ok'     => false,
                'status' => isset($result['reason'])
                    ? $result['reason']
                    : HrchrProgressService::STATUS_QUERY_FAILED,
            ));
        }

        $items = isset($result['items']) && is_array($result['items']) ? $result['items'] : array();

        log_message('info', sprintf(
            'HRCHR progress API ok: items=%d limit=%d offset=%d',
            count($items),
            (int) $filters['limit'],
            (int) $filters['offset']
        ));

        return $this->reply(200, array(
            'ok'     => true,
            'items'  => $items,
            'limit'  => (int) $filters['limit'],
            'offset' => (int) $filters['offset'],
            'count'  => count($items),
        ));
    }

    /**
     * Verify the request carries a valid service credential. On
     * failure, writes the JSON error envelope + status code and
     * returns false. Mirrors Enrollments::enforceServiceAuth.
     *
     * @return bool
     */
    private function enforceServiceAuth()
    {
        $result = $this->serviceAuth->verifyRequest();
        if (!empty($result['ok'])) {
            return true;
        }

        $reason = isset($result['reason']) ? $result['reason'] : HrchrServiceAuth::REASON_INVALID;
        $status = $reason === HrchrServiceAuth::REASON_NOT_CONFIGURED ? 503 : 401;

        log_message('error', 'HRCHR progress API service-auth refused: ' . $reason);

        $this->reply($status, array(
            'ok'     => false,
            'status' => $reason,
        ));

        return false;
    }

    /**
     * @param int $status
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


