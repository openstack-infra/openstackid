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

    public function access_tokens()
    {
        return $this->hasMany('AccessToken');
    }

    public function refresh_tokens()
    {
        return $this->hasMany('RefreshToken');
    }


    public function user()
    {
        return $this->belongsTo('auth\User');
    }

    public function resource_server()
    {
        return $this->belongsTo('ResourceServer');
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
        $scopes = $this->scopes()
            ->with('api')
            ->with('api.resource_server')
            ->where('active','=',true)
            ->orderBy('api_id')->get();

        $res = array();

        foreach($scopes as $db_scope){
            $api             = !is_null($db_scope)?$db_scope->api()->first():null;
            $resource_server = !is_null($api) ? $api->resource_server()->first():null;
            if(!is_null($resource_server) && $resource_server->active && !is_null($api) && $api->active)
                array_push($res,$db_scope);
        }
        return $res;
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
            //check if desired scope belongs to application given scopes
            $db_scope        = $this->scopes()->where('name', '=', $desired_scope)->where('active', '=', true)->first();
            $api             = !is_null($db_scope)?$db_scope->api()->first():null;
            $resource_server = !is_null($api) ? $api->resource_server()->first():null;
            if(is_null($db_scope) ||(!is_null($api) && !$api->active) || (!is_null($resource_server) && !$resource_server->active)){
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
        if(! filter_var($uri, FILTER_VALIDATE_URL)) return false;
        $parts = @parse_url($uri);
        if ($parts === false) {
            return false;
        }
        if($parts['scheme']!=='https')
            return false;
        $client_authorized_uri = ClientAuthorizedUri::where('client_id', '=', $this->id)->where('uri','=',$uri)->first();
        if(is_null($client_authorized_uri)){
            $aux_uri = $parts['scheme'].'://'.strtolower($parts['host']).strtolower($parts['path']);
            $client_authorized_uri = ClientAuthorizedUri::where('client_id', '=', $this->id)->where('uri','=',$aux_uri)->first();
            return !is_null($client_authorized_uri);
        }
        return true;
    }

    public function getApplicationName()
    {
        return $this->app_name;
    }

    public function getApplicationLogo()
    {
        $app_logo = $this->app_logo;
        if(is_null($app_logo) || empty($app_logo))
            $app_logo = asset('img/oauth2.default.logo.png');
        return $app_logo;
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

    public function isLocked()
    {
        return $this->locked;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function isResourceServerClient()
    {
        $id = $this->resource_server_id;
        return !is_null($id);
    }

    public function getResourceServer()
    {
        return $this->resource_server()->first();
    }
}
