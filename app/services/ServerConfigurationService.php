<?php

namespace services;

use Exception;
use openid\services\IServerConfigurationService as IOpenIdServerConfigurationService;
use ServerConfiguration;
use utils\services\IServerConfigurationService;

class ServerConfigurationService implements IOpenIdServerConfigurationService,IServerConfigurationService
{

    const DefaultAssetsUrl = 'http://www.openstack.org/';
    const DefaultPrivateAssociationLifetime = 120;
    const DefaultSessionAssociationLifetime = 21600;
    const DefaultMaxFailedLoginAttempts = 10;
    const DefaultMaxFailedLoginAttempts2ShowCaptcha = 3;
    const DefaultNonceLifetime = 360;
    private $private_association_lifetime;
    private $session_association_lifetime;
    private $max_failed_login_attempts;
    private $max_failed_login_attempts_2_show_captcha;
    private $nonce_lifetime;
    private $assets_url;
    private $redis;
    private $default_config_params;

    public function __construct()
    {
        //todo: remove all specific methods per key and use getConfigValue
        $this->private_association_lifetime = null;
        $this->session_association_lifetime = null;
        $this->max_failed_login_attempts = null;
        $this->max_failed_login_attempts_2_show_captcha = null;
        $this->nonce_lifetime = null;
        $this->assets_url = null;


        $this->redis = \RedisLV4::connection();

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
     * get config value from redis and if not in redis check for it on table server_configuration
     * @param $key
     * @return mixed
     */
    public function getConfigValue($key)
    {
        $res = null;
        try {

            if (!$this->redis->exists($key)) {
                $conf = ServerConfiguration::where('key', '=', $key)->first();
                if ($conf)
                    $this->redis->setnx($key, $conf->value);
                else
                if (isset($this->default_config_params[$key]))
                    $this->redis->setnx($key, $this->default_config_params[$key]);
                else
                    return null;
            }

            $res = $this->redis->get($key);

        } catch (Exception $ex) {
            Log::error($ex);
            if (isset($this->default_config_params[$key])) {
                $res = $this->default_config_params[$key];
            }

        }
        return $res;
    }

}