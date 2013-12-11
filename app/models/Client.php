<?php
use oauth2\models\IClient;

class Client extends Eloquent implements IClient {

    protected $table = 'oauth2_client';

    public function getFriendlyClientType(){
        switch($this->client_type){
            case IClient::ClientType_Confidential:
                return 'Web Application';
            break;
            default:
                return 'Browser (JS Client)';
                break;
        }
    }

    public function user()
    {
        return $this->belongsTo('auth\OpenIdUser');
    }

    public function scopes()
    {
        return $this->belongsToMany('ApiScope','oauth2_client_api_scope','client_id','scope_id');
    }

    public function authorized_uris()
    {
        return $this->hasMany('ClientAuthorizedUri','client_id');
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
        return $this->scopes()->get();
    }

    public function getClientRegisteredUris()
    {
        return $this->authorized_uris()->get();
    }

    public function isScopeAllowed($scope)
    {
        $res = true;
        $desired_scopes = explode(" ",$scope);
        foreach($desired_scopes as $desired_scope){
            $db_scope = $this->scopes()->where('name', '=', $desired_scope)->where('active', '=', true)->first();
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

    public function getApplicationName()
    {
        return $this->app_name;
    }

    public function getApplicationLogo()
    {
        return $this->app_logo;
    }

    public function getApplicationDescription()
    {
        return $this->app_description;
    }

    public function getDeveloperEmail()
    {
        $user = $this->user()->first();
        $email = $user->external_id;
        return $email;
    }

    public function getUserId(){
        $user = $this->user()->first();
        return $user->id;
    }

    public function getId()
    {
        return $this->id;
    }
}