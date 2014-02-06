<?php

namespace services;

use Exception;
use openid\services\IServerConfigurationService as IOpenIdServerConfigurationService;
use ServerConfiguration;
use utils\services\ICacheService;
use utils\services\IServerConfigurationService;
use DB;
use Config;

/**
 * Class ServerConfigurationService
 * @package services
 */
class ServerConfigurationService implements IOpenIdServerConfigurationService, IServerConfigurationService
{

    const DefaultAssetsUrl = 'http://www.openstack.org/';
    const DefaultPrivateAssociationLifetime = 120;
    const DefaultSessionAssociationLifetime = 21600;
    const DefaultMaxFailedLoginAttempts = 10;
    const DefaultMaxFailedLoginAttempts2ShowCaptcha = 3;
    const DefaultNonceLifetime = 360;

    private $default_config_params;

    private $cache_service;

    /**
     * @param ICacheService $cache_service
     */
    public function __construct(ICacheService $cache_service)
    {

        $this->cache_service         = $cache_service;
        $this->default_config_params = array();
        //default config values

        //general
        $this->default_config_params["MaxFailed.Login.Attempts"]             = Config::get('server.MaxFailed_Login_Attempts', 10);
        $this->default_config_params["MaxFailed.LoginAttempts.2ShowCaptcha"] = Config::get('server.MaxFailed_LoginAttempts_2ShowCaptcha', 3);
        $this->default_config_params["Assets.Url"] = 'http://www.openstack.org/';

        //openid
        $this->default_config_params["OpenId.Private.Association.Lifetime"] = 240;
        $this->default_config_params["OpenId.Session.Association.Lifetime"] = 21600;
        $this->default_config_params["OpenId.Nonce.Lifetime"] = 360;

        //policies

        $this->default_config_params["BlacklistSecurityPolicy.BannedIpLifeTimeSeconds"] = 21600;
        $this->default_config_params["BlacklistSecurityPolicy.MinutesWithoutExceptions"] = 5;
        $this->default_config_params["BlacklistSecurityPolicy.ReplayAttackExceptionInitialDelay"] = 10;
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidNonceAttempts"] = 10;
        $this->default_config_params["BlacklistSecurityPolicy.InvalidNonceInitialDelay"] = 10;
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidOpenIdMessageExceptionAttempts"]           = 10;
        $this->default_config_params["BlacklistSecurityPolicy.InvalidOpenIdMessageExceptionInitialDelay"]          = 10;
        $this->default_config_params["BlacklistSecurityPolicy.MaxOpenIdInvalidRealmExceptionAttempts"]             = 10;
        $this->default_config_params["BlacklistSecurityPolicy.OpenIdInvalidRealmExceptionInitialDelay"]            = 10;
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidOpenIdMessageModeAttempts"]                = 10;
        $this->default_config_params["BlacklistSecurityPolicy.InvalidOpenIdMessageModeInitialDelay"]               = 10;
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidOpenIdAuthenticationRequestModeAttempts"]  = 10;
        $this->default_config_params["BlacklistSecurityPolicy.InvalidOpenIdAuthenticationRequestModeInitialDelay"] = 10;
        $this->default_config_params["BlacklistSecurityPolicy.MaxAuthenticationExceptionAttempts"]                 = 10;
        $this->default_config_params["BlacklistSecurityPolicy.AuthenticationExceptionInitialDelay"]                = 20;
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidAssociationAttempts"]                      = 10;
        $this->default_config_params["BlacklistSecurityPolicy.InvalidAssociationInitialDelay"]                     = 20;

        //oauth2

        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.MaxAuthCodeReplayAttackAttempts"]          = 3;
        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.AuthCodeReplayAttackInitialDelay"]         = 10;

        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.MaxInvalidAuthorizationCodeAttempts"]      = 3;
        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.InvalidAuthorizationCodeInitialDelay"]     = 10;

        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.MaxInvalidBearerTokenDisclosureAttempt"]   = 3;
        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.BearerTokenDisclosureAttemptInitialDelay"] = 10;


        $this->default_config_params["OAuth2.AuthorizationCode.Lifetime"] = 600;
        $this->default_config_params["OAuth2.AccessToken.Lifetime"]       = 3600;
        //infinite by default
        $this->default_config_params["OAuth2.RefreshToken.Lifetime"]      = 0;

        //oauth2 policy defaults
        $this->default_config_params["OAuth2SecurityPolicy.MinutesWithoutExceptions"]                    = 2;
        $this->default_config_params["OAuth2SecurityPolicy.MaxBearerTokenDisclosureAttempts"]            = 5;
        $this->default_config_params["OAuth2SecurityPolicy.MaxInvalidClientExceptionAttempts"]           = 10;
        $this->default_config_params["OAuth2SecurityPolicy.MaxInvalidRedeemAuthCodeAttempts"]            = 10;
        $this->default_config_params["OAuth2SecurityPolicy.MaxInvalidInvalidClientCredentialsAttempts"]  = 5;

    }

    public function getUserIdentityEndpointURL($identifier)
    {
        $url = action("UserController@getIdentity", array("identifier" => $identifier));
        return $url;
    }

    public function getOPEndpointURL()
    {
        $url = action("OpenIdProviderController@endpoint");
        return $url;
    }

     /**
     * get config value from cache and if not in cache check for it on table server_configuration
     * @param $key
     * @return mixed
     */
    public function getConfigValue($key)
    {
        $res = null;
        DB::transaction(function () use ($key,&$res) {
            try {

                if (!$this->cache_service->exists($key)) {

                    if (!is_null($conf = ServerConfiguration::where('key', '=', $key)->first()))
                        $this->cache_service->addSingleValue($key, $conf->value);
                    else
                        if (isset($this->default_config_params[$key]))
                            $this->cache_service->addSingleValue($key, $this->default_config_params[$key]);
                        else{
                            $res = null;
                            return;
                        }
                }
                $res = $this->cache_service->getSingleValue($key);

            } catch (Exception $ex) {
                Log::error($ex);
                if (isset($this->default_config_params[$key])) {
                    $res = $this->default_config_params[$key];
                }
            }
        });

        return $res;
    }

    public function getAllConfigValues()
    {
        // TODO: Implement getAllConfigValues() method.
    }

    public function saveConfigValue($key, $value)
    {
        $res = false;
        DB::transaction(function () use ($key, $value,&$res) {
            $conf = ServerConfiguration::where('key', '=', $key)->first();
            if(is_null($conf)){
                $conf = new ServerConfiguration();
                $conf->key = $key;
                $conf->value = $value;
                $res=$conf->Save();
            }
            else{
                $conf->value = $value;
                $res = $conf->Save();
            }
            $this->cache_service->delete($key);
        });
        return $res;
    }
}