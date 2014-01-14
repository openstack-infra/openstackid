<?php

class RefreshToken extends Eloquent {

    protected $table = 'oauth2_refresh_token';

    protected $fillable = array('value', 'from_ip', 'lifetime','scope','audience','void','created_at','updated_at','client_id');

    public function access_tokens()
    {
        return $this->hasMany('AccessToken');
    }

    public function client(){
        return $this->belongsTo('Client');
    }

} 