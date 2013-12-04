<?php
use oauth2\models\IClient;

class Client extends Eloquent implements IClient {

    protected $table = 'oauth2_client';

    public function scopes()
    {
        return $this->belongsToMany('ApiScope','oauth2_client_api_scope','client_id','scope_id');
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function getClientSecret()
    {
        return $this->client_secret;
    }

    public function getClientType()
    {
        return $this->client_type;
    }

    public function getClientAuthorizedRealms()
    {
        // TODO: Implement getClientAuthorizedRealms() method.
    }

    public function getClientScopes()
    {
        // TODO: Implement getClientScopes() method.
    }

    public function getClientRegisteredUris()
    {
        // TODO: Implement getClientRegisteredUris() method.
    }

    public function isScopeAllowed($scope)
    {
        $res = true;
        $desired_scopes = explode(" ",$scope);
        foreach($desired_scopes as $desired_scope){
            $db_scope = $this->scopes()->where('name', '=', $desired_scope)->first();
            if(is_null($db_scope)){
                $res = false;
                break;
            }
        }
        return $res;
    }

    public function isRealmAllowed($realm)
    {
        return false;
    }

    public function isUriAllowed($uri)
    {
        $uri = ClientAuthorizedUri::where('client_id', '=', $this->id)->where('uri','=',$uri)->first();
        return !is_null($uri);
    }
}