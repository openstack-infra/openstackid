<?php

use oauth2\models\IClient;
use utils\model\BaseModelEloquent;

class Client extends BaseModelEloquent implements IClient {

    protected $table = 'oauth2_client';

	public function getActiveAttribute(){
		return (bool) $this->attributes['active'];
	}

	public function getIdAttribute(){
		return (int) $this->attributes['id'];
	}

	public function getLockedAttribute(){
		return (int) $this->attributes['locked'];
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

    public function allowed_origins()
    {
        return $this->hasMany('ClientAllowedOrigin','client_id');
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


    public function isUriAllowed($uri)
    {
        if(!filter_var($uri, FILTER_VALIDATE_URL)) return false;
        $parts = @parse_url($uri);
        if ($parts == false) {
            return false;
        }
        if(($parts['scheme']!=='https') && (ServerConfigurationService::getConfigValue("SSL.Enable")))
            return false;
        $client_authorized_uri = ClientAuthorizedUri::where('client_id', '=', $this->id)->where('uri','=',$uri)->first();
        if(!is_null($client_authorized_uri)) return true;

        if(isset($parts['path'])){
            $aux_uri = $parts['scheme'].'://'.strtolower($parts['host']).strtolower($parts['path']);
            $client_authorized_uri = ClientAuthorizedUri::where('client_id', '=', $this->id)->where('uri','=',$aux_uri)->first();
            return !is_null($client_authorized_uri);
        }
        return false;

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

    public function getApplicationType()
    {
        return $this->application_type;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getFriendlyApplicationType(){
        switch($this->application_type){
            case IClient::ApplicationType_JS_Client:
                return 'Client Side (JS)';
            break;
            case IClient::ApplicationType_Service:
                return 'Service Account';
            break;
            case IClient::ApplicationType_Web_App:
                return 'Web Server Application';
            break;
        }
        throw new Exception('Invalid Application Type');
    }

    public function getClientAllowedOrigins()
    {
        return $this->allowed_origins()->get();
    }

    /**
     * the origin is the triple {protocol, host, port}
     * @param $origin
     * @return bool
     */
    public function isOriginAllowed($origin)
    {
        if(!filter_var($origin, FILTER_VALIDATE_URL)) return false;
        $parts = @parse_url($origin);
        if ($parts == false) {
            return false;
        }
        if($parts['scheme']!=='https')
            return false;
        $origin_without_port = sprinf("%sː//%s",$parts['scheme'],$parts['host']);
        $client_allowed_origin  = $this->allowed_origins()->where('allowed_origin','=',$origin_without_port)->first();
        if(!is_null($client_allowed_origin)) return true;
        if(isset($parts['port'])){
           $origin_with_port    = sprinf("%sː//%s:%s",$parts['scheme'],$parts['host'],$parts['port']);
           $client_authorized_uri = $this->allowed_origins()->where('allowed_origin','=',$origin_with_port)->first();;
           return !is_null($client_authorized_uri);
        }
        return false;
    }

    public function getWebsite()
    {
        return $this->website;
    }
}
