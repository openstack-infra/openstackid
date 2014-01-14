<?php

class UserAction extends Eloquent
{

    protected $table = 'user_actions';

    public function user()
    {
        return $this->belongsTo("User");
    }
}