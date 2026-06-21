<?php

namespace App\Models;

class LegacyInput
{
    public function post($key = null) { $request = service('request'); return $key === null ? $request->getPost() : $request->getPost($key); }
    public function get($key = null) { $request = service('request'); return $key === null ? $request->getGet() : $request->getGet($key); }
    public function get_post($key = null) { return service('request')->getVar($key); }
    public function server($key = null) { return $key === null ? $_SERVER : ($_SERVER[$key] ?? null); }
    public function ip_address() { return service('request')->getIPAddress(); }
}


