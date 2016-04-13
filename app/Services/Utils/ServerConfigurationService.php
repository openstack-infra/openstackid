<?php namespace Services\Utils;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

use Exception;
use Illuminate\Support\Facades\Config;
use Models\ServerConfiguration;
use OpenId\Services\IServerConfigurationService;
use Utils\Db\ITransactionService;
use Utils\Services\ICacheService;
use Utils\Services\IServerConfigurationService as IOpenIdServerConfigurationService;

/**
 * Class ServerConfigurationService
 * @package Services\Utils
 */
class ServerConfigurationService implements IOpenIdServerConfigurationService, IServerConfigurationService
{

    const DefaultAssetsUrl                          = 'https://www.openstack.org/';
    const DefaultPrivateAssociationLifetime         = 120;
    const DefaultSessionAssociationLifetime         = 21600;
    const DefaultMaxFailedLoginAttempts             = 10;
    const DefaultMaxFailedLoginAttempts2ShowCaptcha = 3;
    const DefaultNonceLifetime                      = 360;

    /**
     * @var array
     */
    private $default_config_params;
    /**
     * @var ITransactionService
     */
    private $tx_service;

    /***
     * @param ICacheService $cache_service
     * @param ITransactionService $tx_service
     */
    public function __construct(ICacheService $cache_service, ITransactionService $tx_service)
    {

        $this->cache_service = $cache_service;
        $this->tx_service = $tx_service;
        $this->default_config_params = array();
        //default config values

        //general
        $this->default_config_params["MaxFailed.Login.Attempts"] = Config::get('server.MaxFailed_Login_Attempts', 10);
        $this->default_config_params["SupportEmail"]             = Config::get('server.Support_Email', 'noreply@openstack.org');

        $this->default_config_params["MaxFailed.LoginAttempts.2ShowCaptcha"] = Config::get('server.MaxFailed_LoginAttempts_2ShowCaptcha',
            3);
        $this->default_config_params["Assets.Url"] = Config::get('server.Assets_Url', self::DefaultAssetsUrl );
        // remember me cookie lifetime (minutes)
        $this->default_config_params["Remember.ExpirationTime"] = Config::get('Remember.ExpirationTime', 120);

        //openid
        $this->default_config_params["OpenId.Private.Association.Lifetime"] = Config::get('server.OpenId_Private_Association_Lifetime',
            240);
        $this->default_config_params["OpenId.Session.Association.Lifetime"] = Config::get('server.OpenId_Session_Association_Lifetime',
            21600);
        $this->default_config_params["OpenId.Nonce.Lifetime"] = Config::get('server.OpenId_Nonce_Lifetime', 360);

        //policies
        $this->default_config_params["BlacklistSecurityPolicy.BannedIpLifeTimeSeconds"] = Config::get('server.BlacklistSecurityPolicy_BannedIpLifeTimeSeconds',
            21600);
        $this->default_config_params["BlacklistSecurityPolicy.MinutesWithoutExceptions"] = Config::get('server.BlacklistSecurityPolicy_MinutesWithoutExceptions',
            5);;
        $this->default_config_params["BlacklistSecurityPolicy.ReplayAttackExceptionInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_ReplayAttackExceptionInitialDelay',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidNonceAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxInvalidNonceAttempts',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.InvalidNonceInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_InvalidNonceInitialDelay',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidOpenIdMessageExceptionAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxInvalidOpenIdMessageExceptionAttempts',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.InvalidOpenIdMessageExceptionInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_InvalidOpenIdMessageExceptionInitialDelay',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.MaxOpenIdInvalidRealmExceptionAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxOpenIdInvalidRealmExceptionAttempts',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.OpenIdInvalidRealmExceptionInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_OpenIdInvalidRealmExceptionInitialDelay',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidOpenIdMessageModeAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxInvalidOpenIdMessageModeAttempts',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.InvalidOpenIdMessageModeInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_InvalidOpenIdMessageModeInitialDelay',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidOpenIdAuthenticationRequestModeAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxInvalidOpenIdAuthenticationRequestModeAttempts',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.InvalidOpenIdAuthenticationRequestModeInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_InvalidOpenIdAuthenticationRequestModeInitialDelay',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.MaxAuthenticationExceptionAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxAuthenticationExceptionAttempts',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.AuthenticationExceptionInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_AuthenticationExceptionInitialDelay',
            20);
        $this->default_config_params["BlacklistSecurityPolicy.MaxInvalidAssociationAttempts"] = Config::get('server.BlacklistSecurityPolicy_MaxInvalidAssociationAttempts',
            10);
        $this->default_config_params["BlacklistSecurityPolicy.InvalidAssociationInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_InvalidAssociationInitialDelay',
            20);

        //oauth2
        $this->default_config_params["OAuth2.Enable"] = Config::get('server.OAuth2_Enable', false);
        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.MaxAuthCodeReplayAttackAttempts"] = Config::get('server.BlacklistSecurityPolicy_OAuth2_MaxAuthCodeReplayAttackAttempts',
            3);
        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.AuthCodeReplayAttackInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_OAuth2_AuthCodeReplayAttackInitialDelay',
            10);

        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.MaxInvalidAuthorizationCodeAttempts"] = Config::get('server.BlacklistSecurityPolicy_OAuth2_MaxInvalidAuthorizationCodeAttempts',
            3);
        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.InvalidAuthorizationCodeInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_OAuth2_InvalidAuthorizationCodeInitialDelay',
            10);

        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.MaxInvalidBearerTokenDisclosureAttempt"] = Config::get('server.BlacklistSecurityPolicy_OAuth2_MaxInvalidBearerTokenDisclosureAttempt',
            3);
        $this->default_config_params["BlacklistSecurityPolicy.OAuth2.BearerTokenDisclosureAttemptInitialDelay"] = Config::get('server.BlacklistSecurityPolicy_OAuth2_BearerTokenDisclosureAttemptInitialDelay',
            10);


        $this->default_config_params["OAuth2.AuthorizationCode.Lifetime"] = Config::get('server.OAuth2_AuthorizationCode_Lifetime', 240);
        $this->default_config_params["OAuth2.AccessToken.Lifetime"] = Config::get('server.OAuth2_AccessToken_Lifetime', 3600);
        $this->default_config_params["OAuth2.IdToken.Lifetime"] = Config::get('server.OAuth2_IdToken_Lifetime', 3600);
        //infinite by default
        $this->default_config_params["OAuth2.RefreshToken.Lifetime"] = Config::get('server.OAuth2_RefreshToken_Lifetime', 0);
        //revoked lifetimes
        $this->default_config_params["OAuth2.AccessToken.Revoked.Lifetime"] = Config::get('server.OAuth2_AccessToken_Revoked_Lifetime', 3600);
        $this->default_config_params["OAuth2.AccessToken.Void.Lifetime"] = Config::get('server.OAuth2_AccessToken_Void_Lifetime', 3600);
        $this->default_config_params["OAuth2.RefreshToken.Revoked.Lifetime"] = Config::get('server.OAuth2_RefreshToken_Revoked_Lifetime', 3600);

        //oauth2 policy defaults
        $this->default_config_params["OAuth2SecurityPolicy.MinutesWithoutExceptions"]            = Config::get('server.OAuth2SecurityPolicy_MinutesWithoutExceptions', 2);
        $this->default_config_params["OAuth2SecurityPolicy.MaxBearerTokenDisclosureAttempts"]    = Config::get('server.OAuth2SecurityPolicy_MaxBearerTokenDisclosureAttempts', 5);
        $this->default_config_params["OAuth2SecurityPolicy.MaxInvalidClientExceptionAttempts"]   = Config::get('server.OAuth2SecurityPolicy_MaxInvalidClientExceptionAttempts', 10);
        $this->default_config_params["OAuth2SecurityPolicy.MaxInvalidRedeemAuthCodeAttempts"]    = Config::get('server.OAuth2SecurityPolicy_MaxInvalidRedeemAuthCodeAttempts', 10);
        $this->default_config_params["OAuth2SecurityPolicy.MaxInvalidClientCredentialsAttempts"] = Config::get('server.OAuth2SecurityPolicy_MaxInvalidClientCredentialsAttempts', 5);
        //ssl
        $this->default_config_params["SSL.Enable"] = Config::get('server.SSL_Enable', true);
    }

    public function getUserIdentityEndpointURL($identifier)
    {
        return action("UserController@getIdentity", array("identifier" => $identifier));
    }

    public function getOPEndpointURL()
    {
        return action("OpenId\OpenIdProviderController@endpoint");
    }

    /**
     * get config value from cache and if not in cache check for it on table server_configuration
     * @param $key
     * @return mixed
     */
    public function getConfigValue($key)
    {
        $res = null;
        $cache_service = $this->cache_service;
        $default_config_params = $this->default_config_params;

        $this->tx_service->transaction(function () use ($key, &$res, &$cache_service, &$default_config_params) {
            try {

                if (!$cache_service->exists($key)) {

                    if (!is_null($conf = ServerConfiguration::where('key', '=', $key)->first())) {
                        $cache_service->addSingleValue($key, $conf->value);
                    } else {
                        if (isset($default_config_params[$key])) {
                            $cache_service->addSingleValue($key, $default_config_params[$key]);
                        } else {
                            $res = null;

                            return;
                        }
                    }
                }
                $res = $cache_service->getSingleValue($key);

            } catch (Exception $ex) {
                Log::error($ex);
                if (isset($default_config_params[$key])) {
                    $res = $default_config_params[$key];
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
        $cache_service = $this->cache_service;

        $this->tx_service->transaction(function () use ($key, $value, &$res, &$cache_service) {

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

            $cache_service->delete($key);
        });

        return $res;
    }

    /**
     * @return string
     */
    public function getSiteUrl()
    {
        return Config::get('app.url');
    }
}
