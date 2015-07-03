<?php

use oauth2\models\IClient;
use utils\model\BaseModelEloquent;
use oauth2\models\TokenEndpointAuthInfo;
use oauth2\models\IdTokenResponseInfo;
use oauth2\models\UserInfoResponseInfo;
use jwa\cryptographic_algorithms\DigitalSignatures_MACs_Registry;
use jwa\cryptographic_algorithms\KeyManagementAlgorithms_Registry;
use jwa\cryptographic_algorithms\ContentEncryptionAlgorithms_Registry;
use oauth2\models\IClientPublicKey;

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
        'redirect_uris',
        'allowed_origins'
    );

    public static  $valid_app_types = array
    (
        IClient::ApplicationType_Service,
        IClient::ApplicationType_JS_Client,
        IClient::ApplicationType_Web_App,
        IClient::ApplicationType_Native
    );

    public static $valid_subject_types = array
    (
        IClient::SubjectType_Public,
        IClient::SubjectType_Pairwise
    );

    protected $table = 'oauth2_client';

    public function getActiveAttribute()
    {
        return (bool) $this->attributes['active'];
    }

    public function getIdAttribute()
    {
        return (int) $this->attributes['id'];
    }

    public function getLockedAttribute()
    {
        return (int) $this->attributes['locked'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function public_keys()
    {
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
    private function infereClientTypeFromAppType($app_type)
    {
        switch($app_type)
        {
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

        foreach($scopes as $db_scope)
        {
            $api             = !is_null($db_scope)?$db_scope->api()->first():null;
            $resource_server = !is_null($api) ? $api->resource_server()->first():null;
            if(!is_null($resource_server) && $resource_server->active && !is_null($api) && $api->active)
                array_push($res,$db_scope);
        }
        return $res;
    }

    public function getRedirectUris()
    {
        return explode(',',$this->redirect_uris);
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
        if ($parts == false)
        {
            return false;
        }
        if(($this->application_type !== IClient::ApplicationType_Native && $parts['scheme']!=='https') && (ServerConfigurationService::getConfigValue("SSL.Enable")))
            return false;
        //normalize uri
        $normalized_uri = $parts['scheme'].'://'.strtolower($parts['host']);
        if(isset($parts['path'])) {
            $normalized_uri .= strtolower($parts['path']);
        }
        // normalize url and remove trailing /
        $normalized_uri = rtrim($normalized_uri, '/');

        return str_contains($this->redirect_uris, $normalized_uri);
    }

    public function getApplicationName()
    {
        return $this->app_name;
    }

    public function getApplicationLogo()
    {
        $app_logo = $this->app_logo;
        if(is_null($app_logo) || empty($app_logo))
            $app_logo = asset('assets/img/oauth2.default.logo.png');
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
    public function getFriendlyApplicationType()
    {
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
        return explode(',', $this->allowed_origins);
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

        if(str_contains($this->allowed_origins,$origin_without_port )) return true;
        if(isset($parts['port'])){
            $origin_with_port    = $parts['scheme'].'://'.$parts['host'].':'.$parts['port'];
            return str_contains($this->allowed_origins, $origin_with_port );
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
        if($this->client_secret_expires_at instanceof \DateTime)
            return $this->client_secret_expires_at;
        return new \DateTime($this->client_secret_expires_at);
    }

    /**
     * @return bool
     */
    public function isClientSecretExpired()
    {
        $now = new \DateTime();
        return $this->getClientSecretExpiration() < $now;
    }

    /**
     * @return string[]
     */
    public function getContacts()
    {
        return explode(',',$this->contacts);
    }

    /**
     * @return int
     */
    public function getDefaultMaxAge()
    {
        return (int)$this->default_max_age;
    }

    /**
     * @return bool
     */
    public function requireAuthTimeClaim()
    {
        return $this->require_auth_time;
    }

    /**
     * @return string
     */
    public function getLogoUri()
    {
        return $this->logo_uri;
    }

    /**
     * @return string
     */
    public function getPolicyUri()
    {
       return $this->policy_uri;
    }

    /**
     * @return string
     */
    public function getTermOfServiceUri()
    {
        return $this->tos_uri;
    }

    /**
     * @return string[]
     */
    public function getPostLogoutUris()
    {
        return explode(',', $this->post_logout_redirect_uris);
    }

    /**
     * @return string
     */
    public function getLogoutUri()
    {
        return $this->logout_uri;
    }

    /**
     * @return IdTokenResponseInfo
     */
    public function getIdTokenResponseInfo()
    {
        return new IdTokenResponseInfo
        (
            DigitalSignatures_MACs_Registry::getInstance()->get($this->id_token_signed_response_alg),
            KeyManagementAlgorithms_Registry::getInstance()->get($this->id_token_encrypted_response_alg),
            ContentEncryptionAlgorithms_Registry::getInstance()->get($this->id_token_encrypted_response_enc)
        );
    }

    /**
     * @return UserInfoResponseInfo
     */
    public function getUserInfoResponseInfo()
    {
        return new UserInfoResponseInfo
        (
            DigitalSignatures_MACs_Registry::getInstance()->get($this->userinfo_signed_response_alg),
            KeyManagementAlgorithms_Registry::getInstance()->get($this->userinfo_encrypted_response_alg),
            ContentEncryptionAlgorithms_Registry::getInstance()->get($this->userinfo_encrypted_response_enc)
        );
    }

    /**
     * @return TokenEndpointAuthInfo
     */
    public function getTokenEndpointAuthInfo()
    {
       return new TokenEndpointAuthInfo(
           $this->token_endpoint_auth_method,
           DigitalSignatures_MACs_Registry::getInstance()->isSupported($this->token_endpoint_auth_signing_alg) ?
               DigitalSignatures_MACs_Registry::getInstance()->get($this->token_endpoint_auth_signing_alg) :
               null
       );
    }

    /**
     * @return string
     */
    public function getSubjectType()
    {
        return $this->subject_type;
    }

    /**
     * @return IClientPublicKey[]
     */
    public function getPublicKeys()
    {
       return $this->public_keys()->get();
    }

    /**
     * @return IClientPublicKey[]
     */
    public function getPublicKeysByUse($use)
    {
        return $this->public_keys()->where('usage','=',$use)->all();
    }

    /**
     * @param string $kid
     * @return IClientPublicKey
     */
    public function getPublicKeyByIdentifier($kid)
    {
        return $this->public_keys()->where('kid','=',$kid)->first();
    }

    /**
     * @param IClientPublicKey $public_key
     * @return $this
     */
    public function addPublicKey(IClientPublicKey $public_key)
    {
       $this->public_keys()->save($public_key);
    }

    /**
     * @return string
     */
    public function getJWKSUri()
    {
       return $this->jwks_uri;
    }

    /**
     * @param string $use
     * @param string $alg
     * @return IClientPublicKey
     */
    public function getCurrentPublicKeyByUse($use, $alg)
    {
        $now = new \DateTime();

        return $this->public_keys()
            ->where('usage','=',$use)
            ->where('alg','=',$alg)
            ->where('active','=', true)
            ->where('valid_from','<=',$now)
            ->where('valid_to','>=',$now)
            ->first();
    }
}