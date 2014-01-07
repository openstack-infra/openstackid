<?php

class RefreshToken extends Eloquent {

    protected $table = 'oauth2_refresh_token';

    public function access_tokens()
    {
        return $this->hasMany('AccessToken');
    }

    public function client(){
        return $this->belongsTo('Client');
    }

} 