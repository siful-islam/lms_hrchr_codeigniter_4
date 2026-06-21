<?php

namespace App\Models;

use CodeIgniter\Model;

abstract class NativeBaseModel extends Model
{
    protected $db;
    protected $session;
    protected $request;

    public function __construct()
    {
        parent::__construct();

        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->request = service('request');
    }

    protected function table(string $table)
    {
        return $this->db->table($table);
    }

    protected function row(string $table, array $where = []): ?object
    {
        $builder = $this->db->table($table);

        if ($where) {
            $builder->where($where);
        }

        return $builder->get()->getRow();
    }

    protected function rowArray(string $table, array $where = []): array
    {
        $row = $this->row($table, $where);
        return $row ? (array) $row : [];
    }

    protected function rows(string $table, array $where = []): array
    {
        $builder = $this->db->table($table);

        if ($where) {
            $builder->where($where);
        }

        return $builder->get()->getResultArray();
    }
}


