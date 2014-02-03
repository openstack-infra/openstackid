<?php

use utils\model\BaseModelEloquent;

class UserAction extends BaseModelEloquent
{

    protected $table = 'user_actions';

    public function user()
    {
        return $this->belongsTo("User");
    }
}