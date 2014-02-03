<?php
use utils\model\BaseModelEloquent;

class BannedIP extends BaseModelEloquent
{
    protected $table = 'banned_ips';

    public function user()
    {
        return $this->belongsTo('auth\User');
    }
} 