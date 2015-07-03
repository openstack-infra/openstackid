<?php

use oauth2\models\IClient;
use utils\model\BaseModelEloquent;
use oauth2\models\TokenEndpointAuthInfo;
use oauth2\models\IdTokenResponseInfo;
use oauth2\models\UserInfoResponseInfo;

/**
 * Class Client
 */
class Client extends BaseModelEloquent implements IClient
{

    protected $fillable = array(
        'app_name',
        'app_description',
        'app_logo',
        'client_id',
        'client_secret',
        'client_type',
        'active',
        'locked',
        'user_id',
        'created_at',
        'updated_at',
        'max_auth_codes_issuance_qty',
        'max_auth_codes_issuance_basis',
        'max_access_token_issuance_qty',
        'max_access_token_issuance_basis',
        'max_refresh_token_issuance_qty',
        'max_refresh_token_issuance_basis',
        'use_refresh_token',
        'rotate_refresh_token',
        'resource_server_id',
        'website',
        'application_type',
        'client_secret_expires_at',
        'contacts',
        'logo_uri',
        'tos_uri',
        'post_logout_redirect_uris',
        'logout_uri',
        'logout_session_required',
        'logout_use_iframe',
        'policy_uri',
        'jwks_uri',
        'default_max_age',
        'require_auth_time',
        'token_endpoint_auth_method',
        'token_endpoint_auth_signing_alg',
        'subject_type',
        'userinfo_signed_response_alg',
        'userinfo_encrypted_response_alg',
        'userinfo_encrypted_response_enc',
        'id_token_signed_response_alg',
        'id_token_encrypted_response_alg',
        'id_token_encrypted_response_enc',
    );

    public static  $valid_app_types = array(
        IClient::ApplicationType_Service,
        IClient::ApplicationType_JS_Client,
        IClient::ApplicationType_Web_App,
        IClient::ApplicationType_Native
    );

    public static $valid_subject_types = array(
        IClient::SubjectType_Public,
        IClient::SubjectType_Pairwise
    );

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function public_keys(){

        return $this->hasMany('ClientPublicKey','oauth2_client_id','id');
    }
    /**
     * @param string $value
     */
    public function setApplicationTypeAttribute($value)
    {
        $this->attributes['application_type'] = strtolower($value);
        $this->attributes['client_type']      = $this->infereClientTypeFromAppType($value);
    }

    /**
     * @param string $app_type
     * @return string
     */
    private function infereClientTypeFromAppType($app_type){
        switch($app_type){
            case IClient::ApplicationType_JS_Client:
            case IClient::ApplicationType_Native:
                return IClient::ClientType_Public;
            break;
            default:
                return IClient::ClientType_Confidential;
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
        //normalize uri
        $normalized_uri = $parts['scheme'].'://'.strtolower($parts['host']);
        if(isset($parts['path'])) {
            $normalized_uri .= strtolower($parts['path']);
        }
        // normalize url and remove trailing /
        $normalized_uri = rtrim($normalized_uri, '/');
        $client_authorized_uri = ClientAuthorizedUri::where('client_id', '=', $this->id)->where('uri','=',$normalized_uri)->first();
        return !is_null($client_authorized_uri);
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
        $user  = $this->user()->first();
        $email = $user->getEmail();
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
            case IClient::ApplicationType_Native:
                return 'Native Application';
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
        $origin_without_port = $parts['scheme'].'://'.$parts['host'];
        $client_allowed_origin  = $this->allowed_origins()->where('allowed_origin','=',$origin_without_port)->first();
        if(!is_null($client_allowed_origin)) return true;
        if(isset($parts['port'])){
            $origin_with_port    = $parts['scheme'].'://'.$parts['host'].':'.$parts['port'];
            $client_authorized_uri = $this->allowed_origins()->where('allowed_origin','=',$origin_with_port)->first();;
            return !is_null($client_authorized_uri);
        }
        return false;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return \DateTime
     */
    public function getClientSecretExpiration()
    {
        // TODO: Implement getClientSecretExpiration() method.
    }

    /**
     * @return bool
     */
    public function isClientSecretExpired()
    {
        // TODO: Implement isClientSecretExpired() method.
    }

    /**
     * @return string[]
     */
    public function getContacts()
    {
        // TODO: Implement getContacts() method.
    }

    /**
     * @return int
     */
    public function getDefaultMaxAge()
    {
        // TODO: Implement getDefaultMaxAge() method.
    }

    /**
     * @return bool
     */
    public function requireAuthTimeClaim()
    {
        // TODO: Implement requireAuthTimeClaim() method.
    }

    /**
     * @return string
     */
    public function getLogoUri()
    {
        // TODO: Implement getLogoUri() method.
    }

    /**
     * @return string
     */
    public function getPolicyUri()
    {
        // TODO: Implement getPolicyUri() method.
    }

    /**
     * @return string
     */
    public function getTermOfServiceUri()
    {
        // TODO: Implement getTermOfServiceUri() method.
    }

    /**
     * @return string
     */
    public function getPostLogoutUri()
    {
        // TODO: Implement getPostLogoutUri() method.
    }

    /**
     * @return string
     */
    public function getLogoutUri()
    {
        // TODO: Implement getLogoutUri() method.
    }

    /**
     * @return IdTokenResponseInfo
     */
    public function getIdTokenResponseInfo()
    {
        // TODO: Implement getIdTokenResponseInfo() method.
    }

    /**
     * @return UserInfoResponseInfo
     */
    public function getUserInfoResponseInfo()
    {
        // TODO: Implement getUserInfoResponseInfo() method.
    }

    /**
     * @return TokenEndpointAuthInfo
     */
    public function getTokenEndpointAuthInfo()
    {
        // TODO: Implement getTokenEndpointAuthInfo() method.
    }

    /**
     * @return string
     */
    public function getSubjectType()
    {
        // TODO: Implement getSubjectType() method.
    }

    /**
     * @return \oauth2\models\IClientPublicKey[]
     */
    public function getPublicKeys()
    {
       return $this->public_keys()->all();
    }

    /**
     * @return \oauth2\models\IClientPublicKey[]
     */
    public function getPublicKeysByUse($use)
    {
        return $this->public_keys()->where('usage','=',$use)->all();
    }

    /**
     * @param string $kid
     * @return \oauth2\models\IClientPublicKey
     */
    public function getPublicKeyByIdentifier($kid)
    {
        return $this->public_keys()->where('kid','=',$kid)->first();
    }

    /**
     * @param \oauth2\models\IClientPublicKey $public_key
     * @return $this
     */
    public function addPublicKey(\oauth2\models\IClientPublicKey $public_key)
    {
       $this->public_keys()->save($public_key);
    }

}
