<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;

class LegacyDatabase
{
    protected $db;
    protected ?BaseBuilder $builder = null;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    protected function newBuilder(string $table): BaseBuilder
    {
        $this->builder = $this->db->table($table);
        return $this->builder;
    }

    protected function builder(): BaseBuilder
    {
        if (! $this->builder) {
            $this->builder = $this->db->table('');
        }
        return $this->builder;
    }

    protected function wrap($result): LegacyResult
    {
        return new LegacyResult($result);
    }

    public function get_where($table, $where = [], $limit = null, $offset = 0): LegacyResult
    {
        $builder = $this->newBuilder($table)->where($where);
        if ($limit !== null) {
            $builder->limit((int) $limit, (int) $offset);
        }
        $result = $builder->get();
        $this->builder = null;
        return $this->wrap($result);
    }

    public function get($table = null, $limit = null, $offset = 0): LegacyResult
    {
        $builder = $table ? $this->newBuilder($table) : $this->builder();
        if ($limit !== null) {
            $builder->limit((int) $limit, (int) $offset);
        }
        $result = $builder->get();
        $this->builder = null;
        return $this->wrap($result);
    }

    public function count_all_results($table = '', bool $reset = true): int
    {
        $builder = $table ? $this->newBuilder($table) : $this->builder();
        $count = $builder->countAllResults($reset);
        if ($reset) $this->builder = null;
        return (int) $count;
    }

    public function count_all($table): int { return (int) $this->db->table($table)->countAll(); }
    public function field_exists($field, $table): bool { return $this->db->fieldExists($field, $table); }
    public function table_exists($table): bool { return $this->db->tableExists($table); }

    public function select($select = '*', $escape = null) { $this->builder()->select($select, $escape); return $this; }
    public function from($table) { $this->newBuilder($table); return $this; }
    public function where($key, $value = null, $escape = null) { $this->builder()->where($key, $value, $escape); return $this; }
    public function or_where($key, $value = null, $escape = null) { $this->builder()->orWhere($key, $value, $escape); return $this; }
    public function where_in($key, $values, $escape = null) { $this->builder()->whereIn($key, $values, $escape); return $this; }
    public function or_where_in($key, $values, $escape = null) { $this->builder()->orWhereIn($key, $values, $escape); return $this; }
    public function like($field, $match = '', $side = 'both', $escape = null, $insensitiveSearch = false) { $this->builder()->like($field, $match, $side, $escape, $insensitiveSearch); return $this; }
    public function or_like($field, $match = '', $side = 'both', $escape = null, $insensitiveSearch = false) { $this->builder()->orLike($field, $match, $side, $escape, $insensitiveSearch); return $this; }
    public function join($table, $cond, $type = '') { $this->builder()->join($table, $cond, $type); return $this; }
    public function order_by($field, $dir = 'ASC') { $this->builder()->orderBy($field, $dir); return $this; }
    public function group_by($field) { $this->builder()->groupBy($field); return $this; }
    public function limit($limit, $offset = 0) { $this->builder()->limit((int) $limit, (int) $offset); return $this; }
    public function group_start() { $this->builder()->groupStart(); return $this; }
    public function or_group_start() { $this->builder()->orGroupStart(); return $this; }
    public function group_end() { $this->builder()->groupEnd(); return $this; }

    public function insert($table, $data = null)
    {
        if ($data === null) return $this->builder()->insert($table);
        return $this->newBuilder($table)->insert($data);
    }
    public function insert_id(): int { return (int) $this->db->insertID(); }
    public function update($table, $data = null, $where = null)
    {
        if ($data === null) return $this->builder()->update($table);
        $builder = $this->newBuilder($table);
        if ($where) $builder->where($where);
        return $builder->update($data);
    }
    public function delete($table, $where = null)
    {
        $builder = $this->newBuilder($table);
        if ($where) $builder->where($where);
        return $builder->delete();
    }
    public function query($sql, $binds = null): LegacyResult { return $this->wrap($this->db->query($sql, $binds)); }
    public function escape($str) { return $this->db->escape($str); }
    public function escape_str($str) { return $this->db->escapeString($str); }

    public function __call($method, $arguments)
    {
        if (method_exists($this->db, $method)) {
            return $this->db->$method(...$arguments);
        }
        if (method_exists($this->builder(), $method)) {
            $result = $this->builder()->$method(...$arguments);
            return $result instanceof BaseBuilder ? $this : $result;
        }
        throw new \BadMethodCallException("Unsupported legacy DB method: {$method}");
    }
}


