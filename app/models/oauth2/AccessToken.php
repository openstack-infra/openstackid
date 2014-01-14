<?php

class AccessToken extends Eloquent {

    protected $fillable = array('value', 'from_ip', 'associated_authorization_code','lifetime','scope','audience','created_at','updated_at','client_id','refresh_token_id');

    protected $table = 'oauth2_access_token';

    public function refresh_token()
    {
        return $this->belongsTo('RefreshToken');
    }

    public function client(){
        return $this->belongsTo('Client');
    }
} 