<?php

namespace App\Controllers;

require_once APPPATH . 'libraries/LmsSsoVerifier.php';

/**
 * LMS Phase 1B: hrchr-core <-> hrchr-lms SSO landing controller.
 *
 *   GET /sso/callback?token=<HS256 JWT minted by hrchr-core>
 *
 * The matching core-side issuer is App\Modules\Lms\Services\LmsSsoTokenService
 * (v5-hrcsoft PR #62). On a valid token this controller resolves or
 * JIT-creates the local LMS user, persists the core<->lms link in
 * `hrchr_lms_user_links`, and hands off to the legacy
 * `user_model->set_login_userdata()` which establishes the CI3 session
 * and redirects to the LMS dashboard.
 *
 * Security:
 *   - Verification is delegated to LmsSsoVerifier. Failures collapse to
 *     a single 401 JSON envelope with a generic reason code. The token,
 *     the signing secret, and individual claim values are NEVER logged.
 *   - Response is `Cache-Control: no-store` so the token cannot sit in
 *     a shared cache and be replayed.
 *   - No `print_r` / `var_dump` / `exit` debug output. Errors return
 *     proper HTTP status + JSON; success delegates to the legacy
 *     redirect path.
 *   - JIT-created users get an unguessable random password (32-byte
 *     hex). The Academy LMS legacy login compares `sha1($password)`
 *     against this column, so the random hex is never reachable via
 *     the form login flow -- only via this SSO path.
 */
class Sso extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set(get_settings('timezone'));

        $this->load->database();
        $this->load->library('session');
        $this->load->model('user_model');
        $this->load->helper(array('url', 'user', 'common'));

        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }

    /**
     * GET /sso/callback?token=...
     */
    public function callback()
    {
        $token  = (string) $this->request->getGet('token');
        $result = (new LmsSsoVerifier())->verify($token);

        if (!$result['ok']) {
            // Misconfiguration is a 503 (server has no signing secret);
            // every other failure mode is a flat 401. The token + reason
            // category are NOT echoed back to the network beyond the
            // generic enum.
            $status = $result['reason'] === LmsSsoVerifier::REASON_NOT_CONFIGURED ? 503 : 401;
            log_message('error', 'LMS SSO callback refused: ' . $result['reason']);
            return $this->errorResponse($status, $result['reason']);
        }

        $claims  = $result['claims'];
        $outcome = $this->resolveOrProvisionUser($claims);

        if ($outcome === null || empty($outcome['lms_user_id'])) {
            log_message('error', 'LMS SSO callback failed to provision user');
            return $this->errorResponse(500, 'provision_failed');
        }

        // For an existing mapping, refresh `last_login_at`, `core_email`,
        // and `core_name`. (For a JIT-created or just-linked user the row
        // already has the latest timestamps from insertUserLink.)
        if (empty($outcome['created']) && empty($outcome['linked'])) {
            $this->touchUserLink((string) $claims['sub'], (int) $outcome['lms_user_id'], $claims);
        }

        // user_model->set_login_userdata() sets the CI3 session and
        // emits its own redirect() (admin/dashboard or home). It is the
        // canonical post-login entry path used by the legacy form login.
        $this->user_model->set_login_userdata((int) $outcome['lms_user_id']);
    }

    /**
     * Resolve the LMS user for the given verified SSO claims.
     *
     * Resolution order:
     *   1) Existing mapping by core_user_id -> return that lms_user_id.
     *   2) Existing local user by email     -> insert mapping, link.
     *   3) JIT-create local LMS user        -> insert mapping.
     *
     * @param  array<string, mixed> $claims
     * @return array{lms_user_id:int, created:bool, linked:bool}|null
     */
    protected function resolveOrProvisionUser(array $claims)
    {
        $coreUserId = (string) $claims['sub'];
        $email      = strtolower(trim((string) $claims['email']));

        $existingLink = $this->db
            ->get_where('hrchr_lms_user_links', array('core_user_id' => $coreUserId))
            ->getRowArray();
        if (is_array($existingLink) && !empty($existingLink['lms_user_id'])) {
            return array(
                'lms_user_id' => (int) $existingLink['lms_user_id'],
                'created'     => false,
                'linked'      => false,
            );
        }

        $existingUser = $this->db
            ->get_where('users', array('email' => $email))
            ->getRowArray();
        if (is_array($existingUser) && !empty($existingUser['id'])) {
            $name = isset($claims['name']) ? trim((string) $claims['name']) : '';
            $this->insertUserLink($coreUserId, (int) $existingUser['id'], $email, $name, $claims);

            return array(
                'lms_user_id' => (int) $existingUser['id'],
                'created'     => false,
                'linked'      => true,
            );
        }

        $newLmsUserId = $this->createSsoUser($email, $claims);
        if ($newLmsUserId === null) {
            return null;
        }

        $name = isset($claims['name']) ? trim((string) $claims['name']) : '';
        $this->insertUserLink($coreUserId, (int) $newLmsUserId, $email, $name, $claims);

        return array(
            'lms_user_id' => (int) $newLmsUserId,
            'created'     => true,
            'linked'      => false,
        );
    }

    /**
     * Insert a new LMS users row for an SSO-validated identity.
     *
     * Defaults role_id=2 (student). The password column is filled with
     * cryptographic-grade random hex so that the legacy form login
     * (which compares sha1($posted_password) against this column) can
     * never accidentally authenticate this account.
     *
     * @return int|null new LMS users.id, or null on insert failure
     */
    protected function createSsoUser($email, array $claims)
    {
        $name = isset($claims['name']) ? trim((string) $claims['name']) : '';

        $firstName = $name !== '' ? $name : 'User';
        $lastName  = '';
        if ($name !== '' && strpos($name, ' ') !== false) {
            $parts     = explode(' ', $name, 2);
            $firstName = trim($parts[0]);
            $lastName  = isset($parts[1]) ? trim($parts[1]) : '';
        }

        $now = time();
        $row = array(
            'first_name'        => $firstName !== '' ? $firstName : 'User',
            'last_name'         => $lastName,
            'email'             => $email,
            'phone'             => '',
            'address'           => '',
            'password'          => bin2hex(random_bytes(32)),
            'company_name'      => '',
            'company_site'      => '',
            'no_of_emp'         => 0,
            'skills'            => json_encode(array()),
            'social_links'      => json_encode(array('facebook' => '', 'twitter' => '', 'linkedin' => '')),
            'biography'         => '',
            'role_id'           => 2,
            'date_added'        => $now,
            'last_modified'     => $now,
            'wishlist'          => json_encode(array()),
            'title'             => '',
            'payment_keys'      => json_encode(array()),
            'verification_code' => '',
            'status'            => 1,
            'is_instructor'     => 0,
            'image'             => '',
            'temp'              => '',
            'sessions'          => json_encode(array()),
        );

        // Delegate to user_model so any future signup-hook behavior
        // (analytics, mail, etc.) applies consistently.
        $newId = $this->user_model->register_user($row);

        return $newId ? (int) $newId : null;
    }

    protected function insertUserLink($coreUserId, $lmsUserId, $email, $name, array $claims)
    {
        $now = time();
        $this->db->table('hrchr_lms_user_links')->insert(array(
            'core_user_id'  => (string) $coreUserId,
            'lms_user_id'   => (int) $lmsUserId,
            'tenant_id'     => isset($claims['tenant_id']) ? (string) $claims['tenant_id'] : null,
            'core_email'    => $email !== '' ? $email : null,
            'core_name'     => $name  !== '' ? $name  : null,
            'role_slug'     => null,
            'created_at'    => $now,
            'updated_at'    => $now,
            'last_login_at' => $now,
        ));
    }

    protected function touchUserLink($coreUserId, $lmsUserId, array $claims)
    {
        $now = time();
        $email = isset($claims['email']) ? strtolower(trim((string) $claims['email'])) : null;
        $name  = isset($claims['name'])  ? trim((string) $claims['name'])              : '';

        $this->db
            ->where('core_user_id', (string) $coreUserId)
            ->update('hrchr_lms_user_links', array(
                'lms_user_id'   => (int) $lmsUserId,
                'tenant_id'     => isset($claims['tenant_id']) ? (string) $claims['tenant_id'] : null,
                'core_email'    => $email !== '' ? $email : null,
                'core_name'     => $name  !== '' ? $name  : null,
                'updated_at'    => $now,
                'last_login_at' => $now,
            ));
    }

    /**
     * Generic JSON error response. NEVER echoes the token, the secret,
     * or any user-supplied input back to the network.
     */
    protected function errorResponse($status, $reason)
    {
        $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'ok'     => false,
                'status' => 'sso_failed',
                'reason' => $reason,
            )));
    }
}


