<?php

namespace App\Models;

class LegacyModel
{
    public $db;
    public $input;
    public $session;
    public $load;
    public $output;
    public $request;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->input = new LegacyInput();
        $this->session = new LegacySession();
        $this->load = new LegacyLoader($this);
        $this->output = new LegacyOutput();
        $this->request = service('request');
    }

    public function __get($name)
    {
        $class = '\\App\\Models\\' . ucfirst($name);
        if (class_exists($class)) {
            $this->{$name} = new $class();
            return $this->{$name};
        }
        return null;
    }
}


