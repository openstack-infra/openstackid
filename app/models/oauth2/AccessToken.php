<?php
/**
 * Class AccessToken
 * Access Token Entity
 */
class AccessToken extends Eloquent {

    protected $fillable = array('value','user_id', 'from_ip', 'associated_authorization_code','lifetime','scope','audience','created_at','updated_at','client_id','refresh_token_id');

    protected $table = 'oauth2_access_token';

    private $friendly_scopes;

    public function refresh_token()
    {
        return $this->belongsTo('RefreshToken');
    }

    public function client(){
        return $this->belongsTo('Client');
    }

    public function user(){
        return $this->belongsTo('auth\User');
    }

    public function isVoid(){
        //check lifetime...
        $created_at = $this->created_at;
        $created_at->add(new DateInterval('PT' . intval($this->lifetime) . 'S'));
        $now        = new DateTime(gmdate("Y-m-d H:i:s", time()));
        return ($now > $created_at);
    }

    public function getFriendlyScopes(){
        return $this->friendly_scopes;
    }

    public function setFriendlyScopes($friendly_scopes){
        $this->friendly_scopes = $friendly_scopes;
    }

    public function getRemainingLifetime()
    {
        //check is refresh token is stills alive... (ZERO is infinite lifetime)
        if (intval($this->lifetime) == 0) return 0;
        $created_at = new DateTime($this->created_at);
        $created_at->add(new DateInterval('PT' . intval($this->lifetime) . 'S'));
        $now = new DateTime(gmdate("Y-m-d H:i:s", time()));
        //check validity...
        if ($now > $created_at)
            return -1;
        $seconds = abs($created_at->getTimestamp() - $now->getTimestamp());;
        return $seconds;
    }
} 