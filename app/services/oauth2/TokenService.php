<?php
/**
 * Copyright 2015 OpenStack Foundation
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

namespace services\oauth2;

use AccessToken as DBAccessToken;
use DB;
use Event;
use jwe\impl\JWEFactory;
use jwe\impl\specs\JWE_ParamsSpecification;
use jwk\IJWK;
use jws\impl\specs\JWS_ParamsSpecification;
use jws\JWSFactory;
use jwt\IBasicJWT;
use jwt\impl\JWTClaimSet;
use jwt\impl\UnsecuredJWT;
use jwt\JWTClaim;
use oauth2\exceptions\AbsentClientException;
use oauth2\exceptions\ExpiredAccessTokenException;
use oauth2\exceptions\InvalidAccessTokenException;
use oauth2\exceptions\InvalidAuthorizationCodeException;
use oauth2\exceptions\InvalidClientCredentials;
use oauth2\exceptions\InvalidGrantTypeException;
use oauth2\exceptions\RecipientKeyNotFoundException;
use oauth2\exceptions\ReplayAttackException;
use oauth2\heuristics\ClientEncryptionKeyFinder;
use oauth2\heuristics\EncryptionClientPublicKeyFinder;
use oauth2\heuristics\EncryptionKeyFinder;
use oauth2\heuristics\SigningClientPublicKeyFinder;
use oauth2\heuristics\SigningKeyFinder;
use oauth2\models\AccessToken;
use oauth2\models\AuthorizationCode;
use oauth2\models\IClient;
use oauth2\models\RefreshToken;
use oauth2\OAuth2Protocol;
use oauth2\repositories\IServerPrivateKeyRepository;
use oauth2\services\Authorization;
use oauth2\services\IClientJWKSetReader;
use oauth2\services\IClientService;
use oauth2\services\IPrincipalService;
use oauth2\services\ISecurityContextService;
use oauth2\services\ITokenService;
use oauth2\services\IUserConsentService;
use RefreshToken as DBRefreshToken;
use RefreshToken as RefreshTokenDB;
use utils\Base64UrlRepresentation;
use utils\ByteUtil;
use utils\db\ITransactionService;
use utils\exceptions\UnacquiredLockException;
use utils\IPHelper;
use utils\json_types\JsonValue;
use utils\json_types\NumericDate;
use utils\json_types\StringOrURI;
use utils\services\IAuthService;
use utils\services\ICacheService;
use utils\services\IdentifierGenerator;
use utils\services\ILockManagerService;
use utils\services\IServerConfigurationService;
use Zend\Crypt\Hash;

/**
 * Class TokenService
 * Provides all Tokens related operations (create, get and revoke)
 * @package services\oauth2
 */
class TokenService implements ITokenService
{
    const ClientAccessTokenPrefixList = '.atokens';
    const ClientAuthCodePrefixList = '.acodes';

    const ClientAuthCodeQty = '.acodes.qty';
    const ClientAuthCodeQtyLifetime = 86400;

    const ClientAccessTokensQty = '.atokens.qty';
    const ClientAccessTokensQtyLifetime = 86400;

    const ClientRefreshTokensQty = '.rtokens.qty';
    const ClientRefreshTokensQtyLifetime = 86400;

    //services

    /**
     * @var IClientService
     */
    private $client_service;
    /**
     * @var ILockManagerService
     */
    private $lock_manager_service;
    /**
     * @var IServerConfigurationService
     */
    private $configuration_service;
    /**
     * @var ICacheService
     */
    private $cache_service;
    /**
     * @var IAuthService
     */
    private $auth_service;
    /**
     * @var IUserConsentService
     */
    private $user_consent_service;
    /**
     * @var IdentifierGenerator
     */
    private $auth_code_generator;

    /**
     * @var IdentifierGenerator
     */
    private $access_token_generator;

    /**
     * @var IdentifierGenerator
     */
    private $refresh_token_generator;

    /**
     * @var IServerPrivateKeyRepository
     */
    private $server_private_key_repository;

    /**
     * @var IClientJWKSetReader
     */
    private $jwk_set_reader_service;
    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * @var ISecurityContextService
     */
    private $security_context_service;

    /**
     * @var IPrincipalService
     */
    private $principal_service;

    /**
     * @param IClientService $client_service
     * @param ILockManagerService $lock_manager_service
     * @param IServerConfigurationService $configuration_service
     * @param ICacheService $cache_service
     * @param IAuthService $auth_service
     * @param IUserConsentService $user_consent_service
     * @param IdentifierGenerator $auth_code_generator
     * @param IdentifierGenerator $access_token_generator
     * @param IdentifierGenerator $refresh_token_generator
     * @param IServerPrivateKeyRepository $server_private_key_repository
     * @param IClientJWKSetReader $jwk_set_reader_service
     * @param ISecurityContextService $security_context_service
     * @param IPrincipalService $principal_service
     * @param ITransactionService $tx_service
     */
    public function __construct(
        IClientService $client_service,
        ILockManagerService $lock_manager_service,
        IServerConfigurationService $configuration_service,
        ICacheService $cache_service,
        IAuthService $auth_service,
        IUserConsentService $user_consent_service,
        IdentifierGenerator $auth_code_generator,
        IdentifierGenerator $access_token_generator,
        IdentifierGenerator $refresh_token_generator,
        IServerPrivateKeyRepository $server_private_key_repository,
        IClientJWKSetReader  $jwk_set_reader_service,
        ISecurityContextService $security_context_service,
        IPrincipalService $principal_service,
        ITransactionService $tx_service
    ) {
        $this->client_service                = $client_service;
        $this->lock_manager_service          = $lock_manager_service;
        $this->configuration_service         = $configuration_service;
        $this->cache_service                 = $cache_service;
        $this->auth_service                  = $auth_service;
        $this->user_consent_service          = $user_consent_service;
        $this->auth_code_generator           = $auth_code_generator;
        $this->access_token_generator        = $access_token_generator;
        $this->refresh_token_generator       = $refresh_token_generator;
        $this->server_private_key_repository = $server_private_key_repository;
        $this->jwk_set_reader_service        = $jwk_set_reader_service;
        $this->security_context_service      = $security_context_service;
        $this->principal_service             = $principal_service;
        $this->tx_service                    = $tx_service;

        $this_var = $this;

        Event::listen('oauth2.client.delete', function ($client_id) use (&$this_var) {
            $this_var->revokeClientRelatedTokens($client_id);
        });

        Event::listen('oauth2.client.regenerate.secret', function ($client_id) use (&$this_var) {
            $this_var->revokeClientRelatedTokens($client_id);
        });
    }

    /**
     * Creates a brand new authorization code
     * @param $user_id
     * @param $client_id
     * @param $scope
     * @param string $audience
     * @param null $redirect_uri
     * @param string $access_type
     * @param string $approval_prompt
     * @param bool $has_previous_user_consent
     * @param string|null $state
     * @param string|null $nonce
     * @return AuthorizationCode
     */
    public function createAuthorizationCode
    (
        $user_id,
        $client_id,
        $scope,
        $audience        = '' ,
        $redirect_uri    = null,
        $access_type     = OAuth2Protocol::OAuth2Protocol_AccessType_Online,
        $approval_prompt = OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto,
        $has_previous_user_consent = false,
        $state = null,
        $nonce = null
    )
    {
        //create model

        $code = $this->auth_code_generator->generate(
            AuthorizationCode::create
            (
                $user_id,
                $client_id,
                $scope,
                $audience,
                $redirect_uri,
                $access_type,
                $approval_prompt, $has_previous_user_consent,
                $this->configuration_service->getConfigValue('OAuth2.AuthorizationCode.Lifetime'),
                $state,
                $nonce
            )
        );

        $hashed_value = Hash::compute('sha256', $code->getValue());
        //stores on cache
        $this->cache_service->storeHash($hashed_value,
            array(
                'client_id'                 => $code->getClientId(),
                'scope'                     => $code->getScope(),
                'audience'                  => $code->getAudience(),
                'redirect_uri'              => $code->getRedirectUri(),
                'issued'                    => $code->getIssued(),
                'lifetime'                  => $code->getLifetime(),
                'from_ip'                   => $code->getFromIp(),
                'user_id'                   => $code->getUserId(),
                'access_type'               => $code->getAccessType(),
                'approval_prompt'           => $code->getApprovalPrompt(),
                'has_previous_user_consent' => $code->getHasPreviousUserConsent(),
                'state'                     => $code->getState(),
                'nonce'                     => $code->getNonce(),
            ), intval($code->getLifetime()));

        //stores brand new auth code hash value on a set by client id...
        $this->cache_service->addMemberSet($client_id . self::ClientAuthCodePrefixList, $hashed_value);

        $this->cache_service->incCounter($client_id . self::ClientAuthCodeQty, self::ClientAuthCodeQtyLifetime);

        return $code;
    }

    /**
     * @param $value
     * @return AuthorizationCode
     * @throws \oauth2\exceptions\ReplayAttackException
     * @throws \oauth2\exceptions\InvalidAuthorizationCodeException
     */
    public function getAuthorizationCode($value)
    {

        $hashed_value = Hash::compute('sha256', $value);

        if (!$this->cache_service->exists($hashed_value))
        {
            throw new InvalidAuthorizationCodeException(sprintf("auth_code %s ", $value));
        }
        try
        {

            $this->lock_manager_service->acquireLock('lock.get.authcode.' . $hashed_value);

            $cache_values = $this->cache_service->getHash($hashed_value, array(
                'user_id',
                'client_id',
                'scope',
                'audience',
                'redirect_uri',
                'issued',
                'lifetime',
                'from_ip',
                'access_type',
                'approval_prompt',
                'has_previous_user_consent',
                'state',
                'nonce'
            ));

            $code = AuthorizationCode::load
            (
                $value,
                $cache_values['user_id'],
                $cache_values['client_id'],
                $cache_values['scope'],
                $cache_values['audience'],
                $cache_values['redirect_uri'],
                $cache_values['issued'],
                $cache_values['lifetime'],
                $cache_values['from_ip'],
                $cache_values['access_type'],
                $cache_values['approval_prompt'],
                $cache_values['has_previous_user_consent'],
                $cache_values['state'],
                $cache_values['nonce']
            );

            return $code;
        }
        catch (UnacquiredLockException $ex1)
        {
            throw new ReplayAttackException
            (
                $value,
                sprintf
                (
                    "invalid auth_code %s.",
                    $value
                )
            );
        }
    }

    /**
     * Creates a brand new access token from a give auth code
     * @param AuthorizationCode $auth_code
     * @param null $redirect_uri
     * @return AccessToken
     */
    public function createAccessToken(AuthorizationCode $auth_code, $redirect_uri = null)
    {

        $access_token = $this->access_token_generator->generate
        (
            AccessToken::create
            (
                    $auth_code,
                    $this->configuration_service->getConfigValue('OAuth2.AccessToken.Lifetime')
            )
        );

        $cache_service  = $this->cache_service;
        $client_service = $this->client_service;
        $auth_service   = $this->auth_service;
        $this_var       = $this;

        return $this->tx_service->transaction(function () use (
            $auth_code,
            $redirect_uri,
            $access_token,
            $cache_service,
            $client_service,
            $auth_service,
            $this_var
        ) {

            $value        = $access_token->getValue();
            $hashed_value = Hash::compute('sha256', $value);
            $client_id    = $access_token->getClientId();
            $user_id      = $access_token->getUserId();
            $client       = $client_service->getClientById($client_id);
            $user         = $auth_service->getUserById($user_id);

            $access_token_db = new DBAccessToken
            (
                array
                (
                    'value' => $hashed_value,
                    'from_ip' => IPHelper::getUserIp(),
                    'associated_authorization_code' => Hash::compute('sha256', $auth_code->getValue()),
                    'lifetime' => $access_token->getLifetime(),
                    'scope' => $access_token->getScope(),
                    'audience' => $access_token->getAudience()
                )
            );

            $access_token_db->client()->associate($client);

            $access_token_db->user()->associate($user);

            $access_token_db->save();
            //check if use refresh tokens...
            if
            (
                $client->use_refresh_token &&
                (
                    $client->getApplicationType() == IClient::ApplicationType_Web_App ||
                    $client->getApplicationType() == IClient::ApplicationType_Native
                ) &&
                (
                    $auth_code->getAccessType() == OAuth2Protocol::OAuth2Protocol_AccessType_Offline ||
                    str_contains($auth_code->getScope(), OAuth2Protocol::OfflineAccess_Scope)
                )
            )
            {
                //but only the first time (approval_prompt == force || not exists previous consent)
                if
                (
                    !$auth_code->getHasPreviousUserConsent() ||
                     $auth_code->getApprovalPrompt() == OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Force
                )
                {
                    $this_var->createRefreshToken($access_token);
                }
            }

            $this_var->storesAccessTokenOnCache($access_token);
            //stores brand new access token hash value on a set by client id...
            $cache_service->addMemberSet($client_id . TokenService::ClientAccessTokenPrefixList, $hashed_value);

            $cache_service->incCounter
            (
                $client_id . TokenService::ClientAccessTokensQty,
                TokenService::ClientAccessTokensQtyLifetime
            );

            return $access_token;
        });


    }

    /**
     * Create a brand new Access Token by params
     * @param $client_id
     * @param $scope
     * @param $audience
     * @param null $user_id
     * @return AccessToken
     */
    public function createAccessTokenFromParams($client_id, $scope, $audience, $user_id = null)
    {

        $access_token   = $this->access_token_generator->generate(AccessToken::createFromParams
            (
                $scope,
                $client_id,
                $audience,
                $user_id,
                $this->configuration_service->getConfigValue('OAuth2.AccessToken.Lifetime')
            )
        );

        $cache_service  = $this->cache_service;
        $client_service = $this->client_service;
        $auth_service   = $this->auth_service;
        $this_var       = $this;

        $this->tx_service->transaction(function () use (
            $client_id,
            $scope,
            $audience,
            $user_id,
            &$access_token,
            &$this_var,
            &$cache_service,
            &$client_service,
            &$auth_service
        ) {


            $value = $access_token->getValue();
            $hashed_value = Hash::compute('sha256', $value);

            $this_var->storesAccessTokenOnCache($access_token);

            $client_id = $access_token->getClientId();
            $client = $client_service->getClientById($client_id);

            //stores in DB
            $access_token_db = new DBAccessToken(
                array(
                    'value' => $hashed_value,
                    'from_ip' => IPHelper::getUserIp(),
                    'lifetime' => $access_token->getLifetime(),
                    'scope' => $access_token->getScope(),
                    'audience' => $access_token->getAudience()
                )
            );

            $access_token_db->client()->associate($client);

            if (!is_null($user_id)) {
                $user = $auth_service->getUserById($user_id);
                $access_token_db->user()->associate($user);
            }

            $access_token_db->Save();

            //stores brand new access token hash value on a set by client id...
            $cache_service->addMemberSet($client_id . TokenService::ClientAccessTokenPrefixList, $hashed_value);
            $cache_service->incCounter($client_id . TokenService::ClientAccessTokensQty,
                TokenService::ClientAccessTokensQtyLifetime);

        });

        return $access_token;
    }


    /**
     * @param RefreshToken $refresh_token
     * @param null $scope
     * @return AccessToken|void
     */
    public function createAccessTokenFromRefreshToken(RefreshToken $refresh_token, $scope = null)
    {


        $cache_service = $this->cache_service;
        $client_service = $this->client_service;
        $configuration_service = $this->configuration_service;
        $auth_service = $this->auth_service;
        $access_token_generator = $this->access_token_generator;
        $this_var       = $this;


        //preserve entire operation on db transaction...
        return $this->tx_service->transaction(function () use (
            $refresh_token,
            $scope,
            $this_var,
            $cache_service,
            $client_service,
            $auth_service,
            $configuration_service,
            $access_token_generator
        ) {

            $refresh_token_value = $refresh_token->getValue();
            $refresh_token_hashed_value = Hash::compute('sha256', $refresh_token_value);
            //clear current access tokens as invalid
            $this_var->clearAccessTokensForRefreshToken($refresh_token->getValue());

            //validate scope if present...
            if (!is_null($scope) && empty($scope)) {
                $original_scope = $refresh_token->getScope();
                $aux_original_scope = explode(' ', $original_scope);
                $aux_scope = explode(' ', $scope);
                //compare original scope with given one, and validate if its included on original one
                //or not
                if (count(array_diff($aux_scope, $aux_original_scope)) !== 0) {
                    throw new InvalidGrantTypeException(sprintf("requested scope %s is not contained on original one %s",
                        $scope, $original_scope));
                }
            } else {
                //get original scope
                $scope = $refresh_token->getScope();
            }

            //create new access token
            $access_token = $access_token_generator->generate(
                AccessToken::createFromRefreshToken(
                    $refresh_token,
                    $scope,
                    $configuration_service->getConfigValue('OAuth2.AccessToken.Lifetime')
                )
            );

            $value = $access_token->getValue();
            $hashed_value = Hash::compute('sha256', $value);

            $this_var->storesAccessTokenOnCache($access_token);

            //get user id
            $user_id = $access_token->getUserId();
            //get current client
            $client_id = $access_token->getClientId();
            $client = $client_service->getClientById($client_id);

            //stores in DB
            $access_token_db = new DBAccessToken(
                array(
                    'value' => $hashed_value,
                    'from_ip' => IPHelper::getUserIp(),
                    'lifetime' => $access_token->getLifetime(),
                    'scope' => $access_token->getScope(),
                    'audience' => $access_token->getAudience()
                )
            );

            //save relationships
            $refresh_token_db = DBRefreshToken::where('value', '=', $refresh_token_hashed_value)->first();
            $access_token_db->refresh_token()->associate($refresh_token_db);

            $access_token_db->client()->associate($client);

            if (!is_null($user_id)) {
                $user = $auth_service->getUserById($user_id);
                $access_token_db->user()->associate($user);
            }

            $access_token_db->Save();

            //stores brand new access token hash value on a set by client id...
            $cache_service->addMemberSet($client_id . TokenService::ClientAccessTokenPrefixList, $hashed_value);
            $cache_service->incCounter($client_id . TokenService::ClientAccessTokensQty,
                TokenService::ClientAccessTokensQtyLifetime);
            return $access_token;
        });
    }

    /**
     * @param AccessToken $access_token
     * @throws \oauth2\exceptions\InvalidAccessTokenException
     */
    public function storesAccessTokenOnCache(AccessToken $access_token)
    {
        //stores in REDIS

        $value = $access_token->getValue();
        $hashed_value = Hash::compute('sha256', $value);

        if ($this->cache_service->exists($hashed_value)) {
            throw new InvalidAccessTokenException;
        }

        $auth_code = !is_null($access_token->getAuthCode()) ? Hash::compute('sha256',
            $access_token->getAuthCode()) : '';
        $refresh_token_value = !is_null($access_token->getRefreshToken()) ? Hash::compute('sha256',
            $access_token->getRefreshToken()->getValue()) : '';
        $user_id = !is_null($access_token->getUserId()) ? $access_token->getUserId() : 0;

        $this->cache_service->storeHash($hashed_value, array(
            'user_id' => $user_id,
            'client_id' => $access_token->getClientId(),
            'scope' => $access_token->getScope(),
            'auth_code' => $auth_code,
            'issued' => $access_token->getIssued(),
            'lifetime' => $access_token->getLifetime(),
            'audience' => $access_token->getAudience(),
            'from_ip' => IPHelper::getUserIp(),
            'refresh_token' => $refresh_token_value
        ), intval($access_token->getLifetime()));
    }

    /**
     * @param DBAccessToken $access_token
     * @throws \oauth2\exceptions\InvalidAccessTokenException
     */
    public function storesDBAccessTokenOnCache(DBAccessToken $access_token)
    {
        //stores in Cache

        if ($this->cache_service->exists($access_token->value)) {
            throw new InvalidAccessTokenException;
        }

        $refresh_token_value = '';
        $refresh_token_db = $access_token->refresh_token()->first();
        if (!is_null($refresh_token_db)) {
            $refresh_token_value = $refresh_token_db->value;
        }

        $user_id = !is_null($access_token->user_id) ? $access_token->user_id : 0;

        $this->cache_service->storeHash($access_token->value, array(
                'user_id' => $user_id,
                'client_id' => $access_token->client_id,
                'scope' => $access_token->scope,
                'auth_code' => $access_token->associated_authorization_code,
                'issued' => $access_token->created_at,
                'lifetime' => $access_token->lifetime,
                'from_ip' => $access_token->from_ip,
                'audience' => $access_token->audience,
                'refresh_token' => $refresh_token_value
            )
            , intval($access_token->lifetime));

    }

    /**
     * @param $value
     * @param bool $is_hashed
     * @return AccessToken
     * @throws InvalidAccessTokenException
     * @throws \Exception
     */
    public function getAccessToken($value, $is_hashed = false)
    {
        $cache_service = $this->cache_service;
        $lock_manager_service = $this->lock_manager_service;
        $configuration_service = $this->configuration_service;
        $this_var = $this;

        return $this->tx_service->transaction(function () use (
            $this_var,
            $value,
            $is_hashed,
            $cache_service,
            $lock_manager_service,
            $configuration_service
        ) {
            //hash the given value, bc tokens values are stored hashed on DB
            $hashed_value = !$is_hashed ? Hash::compute('sha256', $value) : $value;
            $lock_name = '';
            $access_token = null;
            try {
                // check cache ...
                if (!$cache_service->exists($hashed_value)) {
                    // check on DB...
                    $access_token_db = DBAccessToken::where('value', '=', $hashed_value)->first();
                    if (is_null($access_token_db)) {
                        if ($cache_service->exists('access.token:void:' . $hashed_value)) // check if its marked on cache as expired ...
                        {
                            throw new ExpiredAccessTokenException(sprintf('Access token %s is expired!', $value));
                        } else {
                            throw new InvalidGrantTypeException(sprintf("Access token %s is invalid!", $value));
                        }
                    }
                    // lock ...
                    $lock_name = 'lock.get.accesstoken.' . $hashed_value;
                    $lock_manager_service->acquireLock($lock_name);
                    if ($access_token_db->isVoid()) {
                        // invalid one ...
                        // add to cache as expired ...
                        $cache_service->addSingleValue('access.token:void:' . $hashed_value,
                            'access.token:void:' . $hashed_value);
                        // and deleted it from db
                        $access_token_db->delete();
                        throw new ExpiredAccessTokenException(sprintf('Access token %s is expired!', $value));
                    }
                    //reload on cache
                    $this_var->storesDBAccessTokenOnCache($access_token_db);
                    //release lock
                    $lock_manager_service->releaseLock($lock_name);
                }

                $cache_values = $cache_service->getHash($hashed_value, array(
                    'user_id',
                    'client_id',
                    'scope',
                    'auth_code',
                    'issued',
                    'lifetime',
                    'from_ip',
                    'audience',
                    'refresh_token'
                ));

                // reload auth code ...
                $auth_code = AuthorizationCode::load
                (
                    $cache_values['auth_code'],
                    intval($cache_values['user_id']) == 0 ? null : intval($cache_values['user_id']),
                    $cache_values['client_id'],
                    $cache_values['scope'],
                    $cache_values['audience'],
                    null,
                    null,
                    $configuration_service->getConfigValue('OAuth2.AuthorizationCode.Lifetime'),
                    $cache_values['from_ip'],
                    $access_type = OAuth2Protocol::OAuth2Protocol_AccessType_Online,
                    $approval_prompt = OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto,
                    $has_previous_user_consent = false,
                    null,
                    null,
                    $is_hashed = true
                );
                // reload access token ...
                $access_token = AccessToken::load
                (
                    $value,
                    $auth_code,
                    $cache_values['issued'],
                    $cache_values['lifetime']
                );
                $refresh_token_value = $cache_values['refresh_token'];

                if (!empty($refresh_token_value)) {
                    $refresh_token = $this_var->getRefreshToken($refresh_token_value, true);
                    $access_token->setRefreshToken($refresh_token);
                }
            } catch (UnacquiredLockException $ex1) {
                throw new InvalidAccessTokenException("access token %s ", $value);
            } catch (ExpiredAccessTokenException $ex2) {
                if (!empty($lock_name)) {
                    $lock_manager_service->releaseLock($lock_name);
                }
            } catch (\Exception $ex) {
                if (!empty($lock_name)) {
                    $lock_manager_service->releaseLock($lock_name);
                }
                throw $ex;
            }

            return $access_token;
        });
    }

    /**
     * Checks if current_ip has access rights on the given $access_token
     * @param AccessToken $access_token
     * @param $current_ip
     * @return bool
     */
    public function checkAccessTokenAudience(AccessToken $access_token, $current_ip)
    {

        $current_audience = $access_token->getAudience();
        $current_audience = explode(' ', $current_audience);
        if (!is_array($current_audience)) {
            $current_audience = array($current_audience);
        }

        return \ResourceServer
            ::where('active', '=', true)
            ->where('ip', '=', $current_ip)
            ->whereIn('host', $current_audience)->count() > 0;
    }


    /**
     * Creates a new refresh token and associate it with given access token
     * @param AccessToken $access_token
     * @return RefreshToken
     */
    public function createRefreshToken(AccessToken &$access_token)
    {
        $refresh_token = $this->refresh_token_generator->generate(
            RefreshToken::create(
                $access_token,
                $this->configuration_service->getConfigValue('OAuth2.RefreshToken.Lifetime')
            )
        );

        $client_service = $this->client_service;
        $auth_service = $this->auth_service;
        $cache_service = $this->cache_service;
        $this_var = $this;

        $this->tx_service->transaction(function () use (
            &$refresh_token,
            &$access_token,
            &$this_var,
            &$client_service,
            &$auth_service,
            &$cache_service
        ) {
            $value = $refresh_token->getValue();
            //hash the given value, bc tokens values are stored hashed on DB
            $hashed_value = Hash::compute('sha256', $value);
            $client_id = $refresh_token->getClientId();
            $user_id = $refresh_token->getUserId();
            $client = $client_service->getClientById($client_id);
            $user = $auth_service->getUserById($user_id);
            //stores in DB
            $refresh_token_db = new DBRefreshToken (
                array(
                    'value' => $hashed_value,
                    'lifetime' => $refresh_token->getLifetime(),
                    'scope' => $refresh_token->getScope(),
                    'from_ip' => IPHelper::getUserIp(),
                    'audience' => $access_token->getAudience(),
                )
            );

            $refresh_token_db->client()->associate($client);
            $refresh_token_db->user()->associate($user);
            $refresh_token_db->Save();
            //associate current access token to refresh token on DB
            $access_token_db = DBAccessToken::where('value', '=',
                Hash::compute('sha256', $access_token->getValue()))->first();
            $access_token_db->refresh_token()->associate($refresh_token_db);
            $access_token_db->Save();

            $access_token->setRefreshToken($refresh_token);

            $cache_service->incCounter($client_id . TokenService::ClientRefreshTokensQty,
                TokenService::ClientRefreshTokensQtyLifetime);
        });

        return $refresh_token;
    }

    /**
     * Get a refresh token by its value
     * @param  $value refresh token value
     * @param $is_hashed
     * @return RefreshToken
     * @throws \oauth2\exceptions\ReplayAttackException
     * @throws \oauth2\exceptions\InvalidGrantTypeException
     */
    public function getRefreshToken($value, $is_hashed = false)
    {
        //hash the given value, bc tokens values are stored hashed on DB
        $hashed_value = !$is_hashed ? Hash::compute('sha256', $value) : $value;

        $refresh_token_db = DBRefreshToken::where('value', '=', $hashed_value)->first();

        if (is_null($refresh_token_db)) {
            throw new InvalidGrantTypeException(sprintf("Refresh token %s does not exists!", $value));
        }

        if ($refresh_token_db->void) {
            throw new ReplayAttackException
            (
                $value,
                sprintf
                (
                    "Refresh token %s is void",
                    $value
                )
            );
        }

        //check is refresh token is stills alive... (ZERO is infinite lifetime)
        if ($refresh_token_db->isVoid()) {
            throw new InvalidGrantTypeException(sprintf("Refresh token %s is expired!", $value));
        }

        $client = $refresh_token_db->client()->first();

        $refresh_token = RefreshToken::load(array(
            'value' => $value,
            'scope' => $refresh_token_db->scope,
            'client_id' => $client->client_id,
            'user_id' => $refresh_token_db->user_id,
            'audience' => $refresh_token_db->audience,
            'from_ip' => $refresh_token_db->from_ip,
            'issued' => $refresh_token_db->created_at,
            'is_hashed' => $is_hashed
        ), intval($refresh_token_db->lifetime));

        return $refresh_token;
    }

    /**
     * Revokes all related tokens to a specific auth code
     * @param $auth_code Authorization Code
     * @return mixed
     */
    public function revokeAuthCodeRelatedTokens($auth_code)
    {
        $auth_code_hashed_value = Hash::compute('sha256', $auth_code);
        $cache_service = $this->cache_service;

        $this->tx_service->transaction(function () use ($auth_code_hashed_value, &$cache_service) {
            //get related access tokens
            $db_access_tokens = DBAccessToken::where('associated_authorization_code', '=',
                $auth_code_hashed_value)->get();

            foreach ($db_access_tokens as $db_access_token) {

                $client = $db_access_tokens->client()->first();
                $access_token_value = $db_access_token->value;
                $refresh_token_db = $db_access_token->refresh_token()->first();

                if (!is_null($refresh_token_db)) {
                    $refresh_token_db->delete();
                }
                //remove auth code from client list on cache
                $cache_service->deleteMemberSet($client->client_id . TokenService::ClientAuthCodePrefixList,
                    $auth_code_hashed_value);
                //remove access token from client list on cache
                $cache_service->deleteMemberSet($client->client_id . TokenService::ClientAccessTokenPrefixList,
                    $access_token_value);
                $cache_service->delete($access_token_value);
                $db_access_token->delete();
            }
        });
    }

    /**
     * Revokes a given access token
     * @param $value
     * @param bool $is_hashed
     * @return bool
     */
    public function revokeAccessToken($value, $is_hashed = false)
    {

        $res = 0;
        $cache_service = $this->cache_service;

        $this->tx_service->transaction(function () use ($value, $is_hashed, &$res, &$cache_service) {
            //hash the given value, bc tokens values are stored hashed on DB
            $hashed_value = !$is_hashed ? Hash::compute('sha256', $value) : $value;

            $access_token_db = DBAccessToken::where('value', '=', $hashed_value)->first();
            $client = $access_token_db->client()->first();
            //delete from cache
            $res = $cache_service->delete($hashed_value);
            $res = $cache_service->deleteMemberSet($client->client_id . TokenService::ClientAccessTokenPrefixList,
                $access_token_db->value);
            //check on DB... and delete it
            $res = $access_token_db->delete();
        });

        return $res > 0;
    }

    /**
     * Revokes all related tokens to a specific client id
     * @param $client_id
     */
    public function revokeClientRelatedTokens($client_id)
    {
        //get client auth codes
        $auth_codes = $this->cache_service->getSet($client_id . self::ClientAuthCodePrefixList);
        //get client access tokens
        $access_tokens = $this->cache_service->getSet($client_id . self::ClientAccessTokenPrefixList);

        $client_service = $this->client_service;
        $cache_service = $this->cache_service;


        $this->tx_service->transaction(function () use (
            $client_id,
            $auth_codes,
            $access_tokens,
            &$cache_service,
            &$client_service
        ) {
            $client = $client_service->getClientById($client_id);
            if (is_null($client)) {
                return;
            }
            //revoke on cache
            $cache_service->deleteArray($auth_codes);
            $cache_service->deleteArray($access_tokens);
            //revoke on db
            $client->access_tokens()->delete();
            $client->refresh_tokens()->delete();
            //delete client list (auth codes and access tokens)
            $cache_service->delete($client_id . TokenService::ClientAuthCodePrefixList);
            $cache_service->delete($client_id . TokenService::ClientAccessTokenPrefixList);
        });
    }


    /**
     * Mark a given refresh token as void
     * @param $value
     * @param bool $is_hashed
     * @return bool
     */
    public function invalidateRefreshToken($value, $is_hashed = false)
    {
        $hashed_value = !$is_hashed ? Hash::compute('sha256', $value) : $value;
        $res = RefreshTokenDB::where('value', '=', $hashed_value)->update(array('void' => true));

        return $res > 0;
    }

    /**
     * Revokes a give refresh token and all related access tokens
     * @param $value
     * @param bool $is_hashed
     * @return mixed
     */
    public function revokeRefreshToken($value, $is_hashed = false)
    {
        $res = false;
        $this_var = $this;

        $this->tx_service->transaction(function () use ($value, $is_hashed, &$res, &$this_var) {
            $res = $this_var->invalidateRefreshToken($value, $is_hashed);
            $res = $res && $this_var->clearAccessTokensForRefreshToken($value, $is_hashed);
        });

        return $res;
    }

    /**
     * Revokes all access tokens for a give refresh token
     * @param $value refresh token value
     * @param bool $is_hashed
     * @return bool|void
     */
    public function clearAccessTokensForRefreshToken($value, $is_hashed = false)
    {

        $hashed_value = !$is_hashed ? Hash::compute('sha256', $value) : $value;
        $res = false;
        $cache_service = $this->cache_service;

        $this->tx_service->transaction(function () use ($hashed_value, &$res, &$cache_service) {

            $refresh_token_db = DBRefreshToken::where('value', '=', $hashed_value)->first();
            if (!is_null($refresh_token_db)) {
                $access_tokens_db = DBAccessToken::where('refresh_token_id', '=', $refresh_token_db->id)->get();
                if (!count($access_tokens_db)) {
                    $res = true;
                }

                foreach ($access_tokens_db as $access_token_db) {

                    $res = $cache_service->delete($access_token_db->value);
                    $client = $access_token_db->client()->first();
                    $res = $cache_service->deleteMemberSet($client->client_id . TokenService::ClientAccessTokenPrefixList,
                        $access_token_db->value);
                    $access_token_db->delete();

                }
            }
        });

        return $res;
    }

    public function getAccessTokenByClient($client_id)
    {
        $client = $this->client_service->getClientById($client_id);
        if (is_null($client)) {
            throw new AbsentClientException(sprintf("client id %d does not exists!", $client_id));
        }
        $res = array();
        $access_tokens = $client->access_tokens()->get();
        foreach ($access_tokens as $access_token) {
            if (!$access_token->isVoid()) {
                array_push($res, $access_token);
            }
        }

        return $res;
    }

    public function getRefreshTokenByClient($client_id)
    {
        $client = $this->client_service->getClientById($client_id);
        if (is_null($client))
        {
            throw new AbsentClientException(sprintf("client id %d does not exists!", $client_id));
        }
        $res = array();
        $refresh_tokens = $client->refresh_tokens()->where('void', '=', false)->get();
        foreach ($refresh_tokens as $refresh_token) {
            if (!$refresh_token->isVoid()) {
                array_push($res, $refresh_token);
            }
        }

        return $res;
    }

    public function getAccessTokenByUserId($user_id)
    {
        $user = $this->auth_service->getUserById($user_id);
        if (is_null($user)) {
            throw new AbsentClientException(sprintf("user id %d does not exists!", $user_id));
        }
        $res = array();
        $access_tokens = $user->access_tokens()->get();
        foreach ($access_tokens as $access_token) {
            if (!$access_token->isVoid()) {
                array_push($res, $access_token);
            }
        }

        return $res;
    }

    public function getRefreshTokenByUserId($user_id)
    {
        $user = $this->auth_service->getUserById($user_id);
        if (is_null($user)) {
            throw new AbsentClientException(sprintf("user id %d does not exists!", $user_id));
        }
        $res = array();
        $refresh_tokens = $user->refresh_tokens()->where('void', '=', false)->get();
        foreach ($refresh_tokens as $refresh_token) {
            if (!$refresh_token->isVoid()) {
                array_push($res, $refresh_token);
            }
        }

        return $res;
    }

    /**
     * @param string $nonce
     * @param string $client_id
     * @param AccessToken|null $access_token
     * @param int $id_token_lifetime
     * @return IBasicJWT
     * @throws AbsentClientException
     * @throws InvalidClientCredentials
     * @throws RecipientKeyNotFoundException
     * @throws \jwt\exceptions\ClaimAlreadyExistsException
     * @throws \oauth2\exceptions\InvalidClientType
     * @throws \oauth2\exceptions\ServerKeyNotFoundException
     */
    public function createIdToken
    (
        $nonce,
        $client_id,
        AccessToken $access_token = null,
        $id_token_lifetime        = 3600
    )
    {
        $client = $this->client_service->getClientById($client_id);

        if (is_null($client))
        {
            throw new AbsentClientException
            (
                sprintf
                (
                    "client id %d does not exists!",
                    $client_id
                )
            );
        }

        $user = $this->auth_service->getCurrentUser();
        // build claim set
        $epoch_now = time();

        $claim_set = new JWTClaimSet
        (
            $iss = new StringOrURI($this->configuration_service->getSiteUrl()),
            $sub = new StringOrURI
            (
                $this->auth_service->wrapUserId
                (
                    $user->getExternalIdentifier(),
                    $client
                )
            ),
            $aud = new StringOrURI($client_id),
            $iat = new NumericDate($epoch_now),
            $exp = new NumericDate($epoch_now + $id_token_lifetime)
        );

        if(!empty($nonce))
            $claim_set->addClaim(new JWTClaim(OAuth2Protocol::OAuth2Protocol_Nonce, new StringOrURI($nonce)));

        $id_token_response_info = $client->getIdTokenResponseInfo();
        $sig_alg                = $id_token_response_info->getSigningAlgorithm();
        $enc_alg                = $id_token_response_info->getEncryptionKeyAlgorithm();
        $enc                    = $id_token_response_info->getEncryptionContentAlgorithm();

        if(!is_null($sig_alg) && !is_null($access_token))
            $this->buildAccessTokenHashClaim($access_token, $sig_alg->getHashingAlgorithm(), $claim_set);

        $this->buildAuthTimeClaim($claim_set);

        $jwt = UnsecuredJWT::fromClaimSet($claim_set);

        if(!is_null($sig_alg))
        {
            // must sign
            // get server private key to sign

            $heuristic = new SigningKeyFinder($this->server_private_key_repository);

            $jwt = self::buildJWS
            (
                $heuristic->find
                (
                    $client,
                    $sig_alg->getName()
                ),
                $sig_alg->getName(),
                $claim_set
            );

        }

        if(!is_null($enc_alg) && !is_null($enc))
        {
            //encrypt , get client public key

            $alg     = new  StringOrURI($enc_alg->getName());
            $enc     = new  StringOrURI($enc->getName());

            //encrypt jwt as payload

            $heuristic = new EncryptionKeyFinder($this->jwk_set_reader_service);

            $jwt = self::buildJWE
            (
                $heuristic->find
                (
                    $client,
                    $alg->getString()
                ),
                $alg,
                $enc,
                $jwt
            );
        }

        return $jwt;
    }

    /**
     * @param IJWK $recipient_key
     * @param StringOrURI $alg
     * @param StringOrURI $enc
     * @param IBasicJWT $jwt
     * @return \jwe\IJWE
     * @throws RecipientKeyNotFoundException
     * @throws \jwk\exceptions\InvalidJWKAlgorithm
     * @throws \jwk\exceptions\InvalidJWKType
     */
    static private function buildJWE(IJWK $recipient_key, StringOrURI $alg, StringOrURI $enc, IBasicJWT $jwt)
    {

        if(is_null($recipient_key))
            throw new RecipientKeyNotFoundException;

        $jwe = JWEFactory::build
        (
            new JWE_ParamsSpecification
            (
                $recipient_key,
                $alg,
                $enc,
                $payload = $jwt->toCompactSerialization()
            )
        );
        return $jwe;
    }

    /**
     * @param IJWK $jwk
     * @param $alg
     * @param JWTClaimSet $claim_set
     * @return \jws\IJWS
     * @throws \jwk\exceptions\InvalidJWKAlgorithm
     * @throws \jwk\exceptions\InvalidJWKType
     */
    static private function buildJWS(IJWK $jwk, $alg, JWTClaimSet $claim_set)
    {
        return JWSFactory::build
        (
            new JWS_ParamsSpecification
            (
                $jwk,
                new StringOrURI
                (
                    $alg
                ),
                $claim_set
            )
        );
    }

    /**
     * @param AccessToken $access_token
     * @param $hashing_alg
     * @param JWTClaimSet $claim_set
     * @return JWTClaimSet
     * @throws InvalidClientCredentials
     * @throws \jwt\exceptions\ClaimAlreadyExistsException
     */
    private function buildAccessTokenHashClaim(AccessToken $access_token, $hashing_alg, JWTClaimSet $claim_set)
    {
        $at                     = $access_token->getValue();
        $at_len                 = intval(substr($hashing_alg,3)) / 2 ;
        $encoder                = new Base64UrlRepresentation();

        if($at_len > ByteUtil::bitLength(strlen($at)))
            throw new InvalidClientCredentials('invalid access token length!.');

        $claim_set->addClaim
        (
            new JWTClaim
            (
                OAuth2Protocol::OAuth2Protocol_AccessToken_Hash,
                new JsonValue
                (
                    $encoder->encode
                    (
                        substr
                        (
                            hash_hmac
                            (
                                $hashing_alg,
                                $at,
                                true
                            ),
                            0,
                            $at_len / 8
                        )
                    )
                )
            )
        );

        return $claim_set;
    }

    private function buildAuthTimeClaim(JWTClaimSet $claim_set)
    {
        if($this->security_context_service->get()->isAuthTimeRequired())
        {
            $claim_set->addClaim
            (
                new JWTClaim
                (
                    OAuth2Protocol::OAuth2Protocol_AuthTime,
                    new JsonValue
                    (
                        $this->principal_service->get()->getAuthTime()
                    )
                )
            );
        }
    }
}