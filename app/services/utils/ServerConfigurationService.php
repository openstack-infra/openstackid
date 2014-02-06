<?php

namespace services\utils;

use Config;
use DB;
use Exception;
use openid\services\IServerConfigurationService as IOpenIdServerConfigurationService;
use ServerConfiguration;
use utils\services\ICacheService;
use utils\services\IServerConfigurationService;

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

        $this->cache_service = $cache_service;
        $this->default_config_params = array();
        //default config values

        //general
        $this->default_config_params["MaxFailed.Login.Attempts"] = Config::get('server.MaxFailed_Login_Attempts', 10);
        $this->default_config_params["MaxFailed.LoginAttempts.2ShowCaptcha"] = Config::get('server.MaxFailed_LoginAttempts_2ShowCaptcha', 3);
        $this->default_config_params["Assets.Url"] = Config::get('server.Assets_Url', 'http://www.openstack.org/');

        //openid
        $this->default_config_params["OpenId.Private.Association.Lifetime"] = Config::get('server.OpenId_Private_Association_Lifetime', 240);
        $this->default_config_params["OpenId.Session.Association.Lifetime"] = Config::get('server.OpenId_Session_Association_Lifetime', 21600);
        $this->default_config_params["OpenId.Nonce.Lifetime"] = Config::get('server.OpenId_Nonce_Lifetime', 360);

        //policies

        $this->default_config_params["BlacklistSecurityPolicy.BannedIpLifeTimeSeconds"] = Config::get('server.BlacklistSecurityPolicy_BannedIpLifeTimeSeconds', 21600);
        $this->default_config_params["BlacklistSecurityPolicy.MinutesWithoutExceptions"] = Config::get('server.BlacklistSecurityPolicy_MinutesWithoutExceptions', 5);;
        $this->default_config_params["BlacklistSecurityPolicy.ReplayAttackExceptionInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_ReplayAttackExceptionInitialDelay', 10);
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidNonceAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxInvalidNonceAttempts', 10);
        $this->default_config_params["BlacklistSecurityPolicy.InvalidNonceInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_InvalidNonceInitialDelay', 10);
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidOpenIdMessageExceptionAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxInvalidOpenIdMessageExceptionAttempts', 10);
        $this->default_config_params["BlacklistSecurityPolicy.InvalidOpenIdMessageExceptionInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_InvalidOpenIdMessageExceptionInitialDelay', 10);
        $this->default_config_params["BlacklistSecurityPolicy.MaxOpenIdInvalidRealmExceptionAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxOpenIdInvalidRealmExceptionAttempts', 10);
        $this->default_config_params["BlacklistSecurityPolicy.OpenIdInvalidRealmExceptionInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_OpenIdInvalidRealmExceptionInitialDelay', 10);
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidOpenIdMessageModeAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxInvalidOpenIdMessageModeAttempts', 10);
        $this->default_config_params["BlacklistSecurityPolicy.InvalidOpenIdMessageModeInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_InvalidOpenIdMessageModeInitialDelay', 10);
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidOpenIdAuthenticationRequestModeAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxInvalidOpenIdAuthenticationRequestModeAttempts', 10);
        $this->default_config_params["BlacklistSecurityPolicy.InvalidOpenIdAuthenticationRequestModeInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_InvalidOpenIdAuthenticationRequestModeInitialDelay', 10);
        $this->default_config_params["BlacklistSecurityPolicy.MaxAuthenticationExceptionAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxAuthenticationExceptionAttempts', 10);
        $this->default_config_params["BlacklistSecurityPolicy.AuthenticationExceptionInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_AuthenticationExceptionInitialDelay', 20);
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidAssociationAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxInvalidAssociationAttempts', 10);
        $this->default_config_params["BlacklistSecurityPolicy.InvalidAssociationInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_InvalidAssociationInitialDelay', 20);

        //oauth2

        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.MaxAuthCodeReplayAttackAttempts"] = Config::get('server.BlacklistSecurityPolicy_OAuth2_MaxAuthCodeReplayAttackAttempts', 3);
        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.AuthCodeReplayAttackInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_OAuth2_AuthCodeReplayAttackInitialDelay', 10);

        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.MaxInvalidAuthorizationCodeAttempts"] = Config::get('server.BlacklistSecurityPolicy_OAuth2_MaxInvalidAuthorizationCodeAttempts', 3);
        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.InvalidAuthorizationCodeInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_OAuth2_InvalidAuthorizationCodeInitialDelay', 10);

        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.MaxInvalidBearerTokenDisclosureAttempt"] = Config::get('server.BlacklistSecurityPolicy_OAuth2_MaxInvalidBearerTokenDisclosureAttempt', 3);
        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.BearerTokenDisclosureAttemptInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_OAuth2_BearerTokenDisclosureAttemptInitialDelay', 10);


        $this->default_config_params["OAuth2.AuthorizationCode.Lifetime"] = Config::get('server.OAuth2_AuthorizationCode_Lifetime', 240);
        $this->default_config_params["OAuth2.AccessToken.Lifetime"] = Config::get('server.OAuth2_AccessToken_Lifetime', 3600);
        //infinite by default
        $this->default_config_params["OAuth2.RefreshToken.Lifetime"] = Config::get('server.OAuth2_RefreshToken_Lifetime', 0);

        //oauth2 policy defaults
        $this->default_config_params["OAuth2SecurityPolicy.MinutesWithoutExceptions"] = Config::get('server.OAuth2SecurityPolicy_MinutesWithoutExceptions', 2);
        $this->default_config_params["OAuth2SecurityPolicy.MaxBearerTokenDisclosureAttempts"] = Config::get('server.OAuth2SecurityPolicy_MaxBearerTokenDisclosureAttempts', 5);
        $this->default_config_params["OAuth2SecurityPolicy.MaxInvalidClientExceptionAttempts"] = Config::get('server.OAuth2SecurityPolicy_MaxInvalidClientExceptionAttempts', 10);
        $this->default_config_params["OAuth2SecurityPolicy.MaxInvalidRedeemAuthCodeAttempts"] = Config::get('server.OAuth2SecurityPolicy_MaxInvalidRedeemAuthCodeAttempts', 10);
        $this->default_config_params["OAuth2SecurityPolicy.MaxInvalidInvalidClientCredentialsAttempts"] = Config::get('server.OAuth2SecurityPolicy_MaxInvalidInvalidClientCredentialsAttempts', 5);
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
        DB::transaction(function () use ($key, &$res) {
            try {

                if (!$this->cache_service->exists($key)) {

                    if (!is_null($conf = ServerConfiguration::where('key', '=', $key)->first()))
                        $this->cache_service->addSingleValue($key, $conf->value);
                    else
                        if (isset($this->default_config_params[$key]))
                            $this->cache_service->addSingleValue($key, $this->default_config_params[$key]);
                        else {
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
        DB::transaction(function () use ($key, $value, &$res) {
            $conf = ServerConfiguration::where('key', '=', $key)->first();
            if (is_null($conf)) {
                $conf = new ServerConfiguration();
                $conf->key = $key;
                $conf->value = $value;
                $res = $conf->Save();
            } else {
                $conf->value = $value;
                $res = $conf->Save();
            }
            $this->cache_service->delete($key);
        });
        return $res;
    }
}