<?php

class Api  extends Eloquent {
    protected $table = 'oauth2_api';

    public function scopes()
    {
        return $this->hasMany('ApiScope','api_id');
    }
} 