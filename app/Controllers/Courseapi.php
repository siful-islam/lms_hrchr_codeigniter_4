<?php

namespace App\Controllers;

require_once APPPATH . 'libraries/HrchrServiceAuth.php';

class Courseapi extends BaseController {

    /** @var HrchrServiceAuth */
    private $serviceAuth;

    public function __construct() {
        parent::__construct();

        $this->load->database();

        header('Content-Type: application/json');
        // Phase 0 hardening: wildcard `Access-Control-Allow-Origin: *` and the
        // open OPTIONS preflight bypass were removed. This is an internal
        // integration surface; cross-origin access must be granted explicitly
        // to the authenticated core service only (Phase 2).

        // Phase 2A hardening: read endpoints now require a service-to-service
        // credential (env LMS_SERVICE_API_SECRET) presented via either
        // `Authorization: Bearer <secret>` or `X-HRCHR-Service-Secret: <secret>`.
        // Phase 2B (in hrchr-core) will mirror the course catalog using this
        // same secret. See application/libraries/HrchrServiceAuth.php.
        $this->serviceAuth = new HrchrServiceAuth();
    }

    public function test() {
        if (!$this->enforceServiceAuth()) {
            return;
        }

        echo json_encode([
            'status' => true,
            'message' => 'Course API is working'
        ]);
    }

    public function courses() {
        if (!$this->enforceServiceAuth()) {
            return;
        }

        $search = $this->request->getGet('search');

        $this->db->select('*');
        $this->db->from('course');

        if (!empty($search)) {
            $this->db->groupStart();
            $this->db->like('title', $search);
            $this->db->orLike('short_description', $search);
            $this->db->orLike('description', $search);
            $this->db->groupEnd();
        }

        $this->db->orderBy('id', 'DESC');
        $query = $this->db->get();

        echo json_encode([
            'status' => true,
            'courses' => $query->getResultArray()
        ]);
    }

    public function course($id = 0) {
        if (!$this->enforceServiceAuth()) {
            return;
        }

        $course = $this->db
            ->where('id', $id)
            ->get('course')
            ->getRowArray();

        echo json_encode([
            'status' => !empty($course),
            'course' => $course
        ]);
    }

    public function save() {
        // Phase 0 hardening: this endpoint previously created a `course` row
        // with NO authentication and a hard-coded user_id/creator/is_admin=1.
        // It is disabled until the integration write path authenticates the
        // caller (LMS_SERVICE_API_SECRET) and derives course ownership from
        // the authenticated hrchr-core identity. Deferred to Phase 2 of the
        // hrchr-lms <-> hrchr-core integration plan
        // (docs/ai/hrchr-lms-core-integration-plan.md).
        // TODO(phase-2): authenticate caller + bind ownership to core identity.
        $this->output->set_status_header(501);
        echo json_encode([
            'status' => false,
            'message' => 'Course write API is disabled pending authenticated core integration (Phase 2).'
        ]);
    }

    public function update($id = 0) {
        // Phase 0 hardening: unauthenticated course mutation is disabled for
        // the same reasons as save() above. Deferred to Phase 2 of the
        // hrchr-lms <-> hrchr-core integration plan
        // (docs/ai/hrchr-lms-core-integration-plan.md).
        // TODO(phase-2): authenticate caller + bind ownership to core identity.
        $this->output->set_status_header(501);
        echo json_encode([
            'status' => false,
            'message' => 'Course write API is disabled pending authenticated core integration (Phase 2).'
        ]);
    }

    /**
     * Verify the request carries a valid service credential. On failure,
     * writes the appropriate JSON error envelope + status code and returns
     * false so the caller can `return;` immediately. The secret, the
     * presented credential, and the failure category are NEVER logged
     * beyond the generic reason enum.
     *
     * @return bool true if the request is authorised, false otherwise.
     */
    private function enforceServiceAuth() {
        $result = $this->serviceAuth->verifyRequest();
        if (!empty($result['ok'])) {
            return true;
        }

        $reason = isset($result['reason']) ? $result['reason'] : HrchrServiceAuth::REASON_INVALID;
        $status = $reason === HrchrServiceAuth::REASON_NOT_CONFIGURED ? 503 : 401;

        log_message('error', 'Course API service-auth refused: ' . $reason);

        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => false,
                'reason' => $reason,
            ]));

        return false;
    }
}


