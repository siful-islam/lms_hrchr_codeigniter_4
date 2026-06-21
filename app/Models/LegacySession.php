<?php

namespace App\Models;

class LegacySession
{
    protected $session;
    public function __construct() { $this->session = service('session'); }
    public function userdata($key = null) { return $key === null ? $this->session->get() : $this->session->get($key); }
    public function set_userdata($key, $value = null) { is_array($key) ? $this->session->set($key) : $this->session->set($key, $value); }
    public function unset_userdata($key) { $this->session->remove($key); }
    public function set_flashdata($key, $value) { $this->session->setFlashdata($key, $value); }
    public function flashdata($key) { return $this->session->getFlashdata($key); }
    public function sess_destroy() { $this->session->destroy(); }
    public function destroy() { $this->session->destroy(); }
    public function get($key = null) { return $key === null ? $this->session->get() : $this->session->get($key); }
    public function set($key, $value = null) { is_array($key) ? $this->session->set($key) : $this->session->set($key, $value); }
    public function remove($key) { $this->session->remove($key); }
}


