<?php

class AccessToken extends Eloquent {

    protected $table = 'oauth2_access_token';

    public function refresh_token()
    {
        return $this->belongsTo('RefreshToken');
    }

    public function client(){
        return $this->belongsTo('Client');
    }
} 