<?php

namespace App\Models;

class LegacyLoader
{
    protected $owner;
    public function __construct($owner) { $this->owner = $owner; }

    public function view($name, $data = [], $return = false)
    {
        $html = view($name, is_array($data) ? $data : []);
        if ($return) return $html;
        echo $html;
        return null;
    }

    public function model($name)
    {
        $normalized = str_replace('\\', '/', $name);
        $parts = explode('/', $normalized);
        $classBase = end($parts);
        $prop = strtolower($classBase);
        $class = '\\App\\Models\\' . str_replace('/', '\\', $normalized);
        if (class_exists($class)) {
            $this->owner->{$prop} = new $class();
        }
    }

    public function library($name)
    {
        $class = '\\App\\Libraries\\' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
        if (class_exists($class)) {
            $prop = strtolower($name);
            $this->owner->{$prop} = new $class();
        }
    }

    public function helper($name) { helper($name); }
    public function database() { $this->owner->db = \Config\Database::connect();  }
}


