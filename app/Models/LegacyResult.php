<?php

namespace App\Models;

class LegacyResult
{
    protected array $rows;
    protected $raw;

    public function __construct($result = null)
    {
        $this->raw = $result;
        if ($result && method_exists($result, 'getResultArray')) {
            $this->rows = $result->getResultArray();
        } elseif (is_array($result)) {
            $this->rows = $result;
        } else {
            $this->rows = [];
        }
    }

    public function result() { return array_map(static fn($r) => (object) $r, $this->rows); }
    public function result_array(): array { return $this->rows; }
    public function row($field = null)
    {
        $row = $this->rows[0] ?? null;
        if ($row === null) return null;
        return $field !== null ? ($row[$field] ?? null) : (object) $row;
    }
    public function row_array(): ?array { return $this->rows[0] ?? null; }
    public function num_rows(): int { return count($this->rows); }
    public function free_result(): void { $this->rows = []; }
}


