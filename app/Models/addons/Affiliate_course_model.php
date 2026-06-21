<?php

namespace App\Models\addons;

use App\Models\NativeBaseModel;

class Affiliate_course_model extends NativeBaseModel
{
    protected $table = 'affiliator';

    public function is_affilator($user_id): int
    {
        if (empty($user_id)) {
            return 0;
        }

        if (! $this->db->tableExists('affiliator')) {
            return 0;
        }

        return $this->db->table('affiliator')
            ->where('user_id', $user_id)
            ->countAllResults() > 0 ? 1 : 0;
    }
}


