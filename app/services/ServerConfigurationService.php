<?php

namespace services;

use Exception;
use openid\services\IServerConfigurationService as IOpenIdServerConfigurationService;
use ServerConfiguration;
use utils\services\ICacheService;
use utils\services\IServerConfigurationService;

/**
 * Class ServerConfigurationService
 * @package services
 */
class ServerConfigurationService implements IOpenIdServerConfigurationService,IServerConfigurationService
{

    const DefaultAssetsUrl = 'http://www.openstack.org/';
    const DefaultPrivateAssociationLifetime = 120;
    const DefaultSessionAssociationLifetime = 21600;
    const DefaultMaxFailedLoginAttempts = 10;
    const DefaultMaxFailedLoginAttempts2ShowCaptcha = 3;
    const DefaultNonceLifetime = 360;

    private $default_config_params;

    private $cache_service;

    public function __construct(ICacheService $cache_service)
    {

        $this->cache_service         = $cache_service;
        //default config values
        $this->default_config_params = array();
        $this->default_config_params["Private.Association.Lifetime"] = 240;
        $this->default_config_params["Session.Association.Lifetime"] = 21600;
        $this->default_config_params["MaxFailed.Login.Attempts"] = 10;
        $this->default_config_params["MaxFailed.LoginAttempts.2ShowCaptcha"] = 3;
        $this->default_config_params["Nonce.Lifetime"] = 360;
        $this->default_config_params["Assets.Url"] = 'http://www.openstack.org/';

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
        $this->default_config_params["OAuth2SecurityPolicy.MinutesWithoutExceptions"]          = 2;
        $this->default_config_params["OAuth2SecurityPolicy.MaxBearerTokenDisclosureAttempts"]  = 5;
        $this->default_config_params["OAuth2SecurityPolicy.MaxInvalidClientExceptionAttempts"] = 10;
        $this->default_config_params["OAuth2SecurityPolicy.MaxInvalidRedeemAuthCodeAttempts"]  = 10;
    }

    public function getUserIdentityEndpointURL($identifier)
    {
        $url = action("UserController@getIdentity", array("identifier" => $identifier));
        return $url;
    }

    public function getOPEndpointURL()
    {
        $url = action("OpenIdProviderController@op_endpoint");
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
        try {

            if (!$this->cache_service->exists($key)) {

                if (!is_null($conf = ServerConfiguration::where('key', '=', $key)->first()))
                    $this->cache_service->addSingleValue($key, $conf->value);
                else
                if (isset($this->default_config_params[$key]))
                    $this->cache_service->addSingleValue($key, $this->default_config_params[$key]);
                else
                    return null;
            }

            $res = $this->cache_service->getSingleValue($key);

        } catch (Exception $ex) {
            Log::error($ex);
            if (isset($this->default_config_params[$key])) {
                $res = $this->default_config_params[$key];
            }
        }
        return $res;
    }

}