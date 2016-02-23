<?php
use utils\model\BaseModelEloquent;

/**
 * Class BannedIP
 */
class BannedIP extends BaseModelEloquent
{
    protected $table = 'banned_ips';

    public function user()
    {
        return $this->belongsTo('auth\User');
    }
} 