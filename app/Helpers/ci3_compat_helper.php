<?php

if (! function_exists('html_escape')) {
    function html_escape($var, $double_encode = true) { return esc($var, 'html', null, $double_encode); }
}
if (! function_exists('base_url')) {
    function base_url($uri = '') { return \CodeIgniter\Config\Services::request()->getUri()->getBaseURL() . ltrim((string)$uri, '/'); }
}
if (! function_exists('site_url')) {
    function site_url($uri = '') { return base_url($uri); }
}
if (! function_exists('redirect')) {
    function redirect($uri = '', $method = 'auto', $code = null) { return redirect()->to($uri); }
}
if (! function_exists('get_instance')) {
    function &get_instance()
    {
        static $instance;

        if (! $instance) {
            $instance = new class {
                public $db;

                public function __construct()
                {
                    $this->db = \Config\Database::connect();
                }

                public function load()
                {
                    return $this;
                }

                public function database()
                {
                    return $this->db;
                }
            };
        }

        return $instance;
    }
}

