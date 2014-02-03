<?php

/**
 * Class RefreshToken
 * Refresh Token Entity
 */
class RefreshToken extends Eloquent {

    protected $table = 'oauth2_refresh_token';

    private $friendly_scopes;

    protected $fillable = array('value', 'from_ip', 'lifetime','scope','audience','void','created_at','updated_at','client_id');

    public function access_tokens()
    {
        return $this->hasMany('AccessToken');
    }

    public function client(){
        return $this->belongsTo('Client');
    }

    public function user(){
        return $this->belongsTo('auth\User');
    }

    public function isVoid(){
        if($this->lifetime === 0) return false;
        //check lifetime...
        $created_at = $this->created_at;
        $created_at->add(new DateInterval('PT' . $this->lifetime . 'S'));
        $now        = new DateTime(gmdate("Y-m-d H:i:s", time()));
        return ($now > $created_at);
    }


    public function getRemainingLifetime()
    {
        //check is refresh token is stills alive... (ZERO is infinite lifetime)
        if ($this->lifetime === 0) return 0;
        $created_at = new DateTime($this->created_at);
        $created_at->add(new DateInterval('PT' . $this->lifetime . 'S'));
        $now = new DateTime(gmdate("Y-m-d H:i:s", time()));
        //check validity...
        if ($now > $created_at)
            return -1;
        $seconds = abs($created_at->getTimestamp() - $now->getTimestamp());;
        return $seconds;
    }

    public function getFriendlyScopes(){
        return $this->friendly_scopes;
    }

    public function setFriendlyScopes($friendly_scopes){
        $this->friendly_scopes = $friendly_scopes;
    }
} 