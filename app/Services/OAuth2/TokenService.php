<?php namespace Services\OAuth2;
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

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use jwa\cryptographic_algorithms\HashFunctionAlgorithm;
use jwt\IBasicJWT;
use jwt\impl\JWTClaimSet;
use jwt\JWTClaim;
use OAuth2\Models\AccessToken;
use Models\OAuth2\AccessToken as AccessTokenDB;
use Models\OAuth2\RefreshToken as RefreshTokenDB;
use Models\OAuth2\ResourceServer;
use OAuth2\Builders\IdTokenBuilder;
use OAuth2\Exceptions\AbsentClientException;
use OAuth2\Exceptions\AbsentCurrentUserException;
use OAuth2\Exceptions\ExpiredAccessTokenException;
use OAuth2\Exceptions\InvalidAccessTokenException;
use OAuth2\Exceptions\InvalidAuthorizationCodeException;
use OAuth2\Exceptions\InvalidClientCredentials;
use OAuth2\Exceptions\InvalidGrantTypeException;
use OAuth2\Exceptions\ReplayAttackAuthCodeException;
use OAuth2\Exceptions\ReplayAttackException;
use OAuth2\Exceptions\ReplayAttackRefreshTokenException;
use OAuth2\Exceptions\RevokedAccessTokenException;
use OAuth2\Exceptions\RevokedRefreshTokenException;
use OAuth2\Models\AuthorizationCode;
use OAuth2\Models\IClient;
use OAuth2\Models\RefreshToken;
use OAuth2\Repositories\IAccessTokenRepository;
use OAuth2\Repositories\IClientRepository;
use OAuth2\Repositories\IRefreshTokenRepository;
use OAuth2\Services\ITokenService;
use OAuth2\OAuth2Protocol;
use OAuth2\Repositories\IServerPrivateKeyRepository;
use OAuth2\Services\IClientJWKSetReader;
use OAuth2\Services\IClientService;
use OAuth2\Services\IPrincipalService;
use OAuth2\Services\ISecurityContextService;
use OAuth2\Services\IUserConsentService;
use utils\Base64UrlRepresentation;
use utils\ByteUtil;
use Utils\Db\ITransactionService;
use Utils\Exceptions\ConfigurationException;
use Utils\Exceptions\UnacquiredLockException;
use Utils\IPHelper;
use utils\json_types\JsonValue;
use utils\json_types\NumericDate;
use utils\json_types\StringOrURI;
use Utils\Services\IAuthService;
use Utils\Services\ICacheService;
use Utils\Services\IdentifierGenerator;
use Utils\Services\ILockManagerService;
use Utils\Services\IServerConfigurationService;
use Zend\Crypt\Hash;
use Exception;

/**
 * Class TokenService
 * Provides all Tokens related operations (create, get and revoke)
 * @package Services\OAuth2
 */
final class TokenService implements ITokenService
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
     * @var IdTokenBuilder
     */
    private $id_token_builder;

    /**
     * @var IClientRepository
     */
    private $client_repository;

    /**
     * @var IAccessTokenRepository
     */
    private $access_token_repository;


    /**
     * @var IRefreshTokenRepository
     */
    private $refresh_token_repository;

    public function __construct
    (
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
        IdTokenBuilder $id_token_builder,
        IClientRepository $client_repository,
        IAccessTokenRepository $access_token_repository,
        IRefreshTokenRepository $refresh_token_repository,
        ITransactionService $tx_service
    )
    {
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
        $this->id_token_builder              = $id_token_builder;
        $this->client_repository             = $client_repository;
        $this->access_token_repository       = $access_token_repository;
        $this->refresh_token_repository      = $refresh_token_repository;
        $this->tx_service                    = $tx_service;



        Event::listen('oauth2.client.delete', function ($client_id) {
            $this->revokeClientRelatedTokens($client_id);
        });

        Event::listen('oauth2.client.regenerate.secret', function ($client_id) {
            $this->revokeClientRelatedTokens($client_id);
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
     * @param string|null $response_type
     * @param string|null $prompt
     * @return AuthorizationCode
     */
    public function createAuthorizationCode
    (
        $user_id,
        $client_id,
        $scope,
        $audience                  = '' ,
        $redirect_uri              = null,
        $access_type               = OAuth2Protocol::OAuth2Protocol_AccessType_Online,
        $approval_prompt           = OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto,
        $has_previous_user_consent = false,
        $state                     = null,
        $nonce                     = null,
        $response_type             = null,
        $prompt                    = null
    )
    {
        //create model

        $code = $this->auth_code_generator->generate
        (
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
                $nonce,
                $response_type,
                $this->security_context_service->get()->isAuthTimeRequired(),
                $this->principal_service->get()->getAuthTime(),
                $prompt
            )
        );

        $hashed_value = Hash::compute('sha256', $code->getValue());
        //stores on cache
        $this->cache_service->storeHash($hashed_value,
            array
            (
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
                'response_type'             => $code->getResponseType(),
                'requested_auth_time'       => $code->isAuthTimeRequested(),
                'auth_time'                 => $code->getAuthTime(),
                'prompt'                    => $code->getPrompt(),
            ), intval($code->getLifetime()));

        //stores brand new auth code hash value on a set by client id...
        $this->cache_service->addMemberSet($client_id . self::ClientAuthCodePrefixList, $hashed_value);

        $this->cache_service->incCounter($client_id . self::ClientAuthCodeQty, self::ClientAuthCodeQtyLifetime);

        return $code;
    }

    /**
     * @param $value
     * @return AuthorizationCode
     * @throws ReplayAttackException
     * @throws InvalidAuthorizationCodeException
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

            $cache_values = $this->cache_service->getHash($hashed_value, array
            (
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
                'nonce',
                'response_type',
                'requested_auth_time',
                'auth_time',
                'prompt',
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
                $cache_values['nonce'],
                $cache_values['response_type'],
                $cache_values['requested_auth_time'],
                $cache_values['auth_time'],
                $cache_values['prompt']
            );

            return $code;
        }
        catch (UnacquiredLockException $ex1)
        {
            throw new ReplayAttackAuthCodeException
            (
                $value,
                sprintf
                (
                    "Code was already redeemed %s.",
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

        $cache_service           = $this->cache_service;
        $client_service          = $this->client_service;
        $auth_service            = $this->auth_service;
        $client_repository       = $this->client_repository;
        $access_token_repository = $this->access_token_repository;

        return $this->tx_service->transaction(function () use (
            $auth_code,
            $redirect_uri,
            $access_token,
            $cache_service,
            $client_service,
            $auth_service,
            $client_repository,
            $access_token_repository
        ) {

            $value        = $access_token->getValue();
            $hashed_value = Hash::compute('sha256', $value);
            //oauth2 client id
            $client_id    = $access_token->getClientId();
            $user_id      = $access_token->getUserId();
            $client       = $client_repository->getClientById($client_id);
            $user         = $auth_service->getUserById($user_id);

            // TODO; move to a factory

            $access_token_db = new AccessTokenDB
            (
                [
                    'value'                         => $hashed_value,
                    'from_ip'                       => IPHelper::getUserIp(),
                    'associated_authorization_code' => Hash::compute('sha256', $auth_code->getValue()),
                    'lifetime'                      => $access_token->getLifetime(),
                    'scope'                         => $access_token->getScope(),
                    'audience'                      => $access_token->getAudience()
                ]
            );

            $access_token_db->client()->associate($client);

            $access_token_db->user()->associate($user);

            $access_token_repository->add($access_token_db);

            //check if use refresh tokens...
            Log::debug
            (
                sprintf
                (
                    'use_refresh_token: %s - app_type: %s - scopes: %s - auth_code_access_type: %s - prompt: %s - approval_prompt: %s',
                    $client->use_refresh_token,
                    $client->getApplicationType(),
                    $auth_code->getScope(),
                    $auth_code->getAccessType(),
                    $auth_code->getPrompt(),
                    $auth_code->getApprovalPrompt()

                )
            );

            if
            (
                $client->use_refresh_token &&
                (
                    $client->getApplicationType() == IClient::ApplicationType_Web_App ||
                    $client->getApplicationType() == IClient::ApplicationType_Native
                ) &&
                (
                    $auth_code->getAccessType() == OAuth2Protocol::OAuth2Protocol_AccessType_Offline ||
                    //OIDC: http://openid.net/specs/openid-connect-core-1_0.html#OfflineAccess
                    str_contains($auth_code->getScope(), OAuth2Protocol::OfflineAccess_Scope)
                )
            )
            {
                //but only the first time (approval_prompt == force || not exists previous consent)
                if
                (
                    !$auth_code->getHasPreviousUserConsent() ||
                     // google oauth2 protocol
                     strpos($auth_code->getApprovalPrompt(),OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Force) !== false ||
                     // http://openid.net/specs/openid-connect-core-1_0.html#OfflineAccess
                     strpos($auth_code->getPrompt(), OAuth2Protocol::OAuth2Protocol_Prompt_Consent) !== false
                )
                {
                    Log::debug('creating refresh token ....');
                    $this->createRefreshToken($access_token);
                }
            }

            $this->storesAccessTokenOnCache($access_token);
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

        $cache_service           = $this->cache_service;
        $client_repository       = $this->client_repository;
        $auth_service            = $this->auth_service;
        $access_token_repository = $this->access_token_repository;

        return $this->tx_service->transaction(function () use (
            $client_id,
            $scope,
            $audience,
            $user_id,
            $access_token,
            $cache_service,
            $client_repository,
            $access_token_repository,
            $auth_service
        ) {


            $value        = $access_token->getValue();
            $hashed_value = Hash::compute('sha256', $value);

            $this->storesAccessTokenOnCache($access_token);

            $client_id = $access_token->getClientId();
            $client    = $client_repository->getClientById($client_id);

            // todo: move to a factory
            $access_token_db = new AccessTokenDB(
                [
                    'value' => $hashed_value,
                    'from_ip' => IPHelper::getUserIp(),
                    'lifetime' => $access_token->getLifetime(),
                    'scope' => $access_token->getScope(),
                    'audience' => $access_token->getAudience()
                ]
            );

            $access_token_db->client()->associate($client);

            if (!is_null($user_id)) {
                $user = $auth_service->getUserById($user_id);
                $access_token_db->user()->associate($user);
            }

            $access_token_repository->add($access_token_db);

            //stores brand new access token hash value on a set by client id...
            $cache_service->addMemberSet($client_id . TokenService::ClientAccessTokenPrefixList, $hashed_value);
            $cache_service->incCounter($client_id . TokenService::ClientAccessTokensQty, TokenService::ClientAccessTokensQtyLifetime);
            return $access_token;
        });

    }

    /**
     * @param RefreshToken $refresh_token
     * @param null $scope
     * @return AccessToken|void
     */
    public function createAccessTokenFromRefreshToken(RefreshToken $refresh_token, $scope = null)
    {

        $cache_service           = $this->cache_service;
        $client_repository       = $this->client_repository;
        $configuration_service   = $this->configuration_service;
        $auth_service            = $this->auth_service;
        $access_token_generator  = $this->access_token_generator;
        $access_token_repository = $this->access_token_repository;

        //preserve entire operation on db transaction...
        return $this->tx_service->transaction(function () use (
            $refresh_token,
            $scope,
            $cache_service,
            $client_repository,
            $auth_service,
            $configuration_service,
            $access_token_repository,
            $access_token_generator
        ) {

            $refresh_token_value         = $refresh_token->getValue();
            $refresh_token_hashed_value  = Hash::compute('sha256', $refresh_token_value);
            //clear current access tokens as invalid
            $this->clearAccessTokensForRefreshToken($refresh_token->getValue());

            //validate scope if present...
            if (!is_null($scope) && empty($scope))
            {
                $original_scope     = $refresh_token->getScope();
                $aux_original_scope = explode(OAuth2Protocol::OAuth2Protocol_Scope_Delimiter, $original_scope);
                $aux_scope          = explode(OAuth2Protocol::OAuth2Protocol_Scope_Delimiter, $scope);
                //compare original scope with given one, and validate if its included on original one
                //or not
                if (count(array_diff($aux_scope, $aux_original_scope)) !== 0)
                {
                    throw new InvalidGrantTypeException
                    (
                        sprintf
                        (
                            "requested scope %s is not contained on original one %s",
                            $scope,
                            $original_scope
                        )
                    );
                }
            }
            else
            {
                //get original scope
                $scope = $refresh_token->getScope();
            }

            //create new access token
            $access_token = $access_token_generator->generate
            (
                AccessToken::createFromRefreshToken
                (
                    $refresh_token,
                    $scope,
                    $configuration_service->getConfigValue('OAuth2.AccessToken.Lifetime')
                )
            );

            $value        = $access_token->getValue();
            $hashed_value = Hash::compute('sha256', $value);

            $this->storesAccessTokenOnCache($access_token);

            //get user id
            $user_id = $access_token->getUserId();
            //get current client
            $client_id = $access_token->getClientId();
            $client    = $client_repository->getClientById($client_id);

            //todo : move to a factory
            $access_token_db = new AccessTokenDB
            (
                array
                (
                    'value'    => $hashed_value,
                    'from_ip'  => IPHelper::getUserIp(),
                    'lifetime' => $access_token->getLifetime(),
                    'scope'    => $access_token->getScope(),
                    'audience' => $access_token->getAudience()
                )
            );

            //save relationships
            $refresh_token_db = RefreshTokenDB::where('value', '=', $refresh_token_hashed_value)->first();
            $access_token_db->refresh_token()->associate($refresh_token_db);

            $access_token_db->client()->associate($client);

            if (!is_null($user_id))
            {
                $user = $auth_service->getUserById($user_id);
                $access_token_db->user()->associate($user);
            }

            $access_token_repository->add($access_token_db);

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
     * @param AccessToken $access_token
     * @throws InvalidAccessTokenException
     */
    public function storesAccessTokenOnCache(AccessToken $access_token)
    {
        //stores in REDIS

        $value        = $access_token->getValue();
        $hashed_value = Hash::compute('sha256', $value);

        if ($this->cache_service->exists($hashed_value)) {
            throw new InvalidAccessTokenException;
        }

        $auth_code = !is_null($access_token->getAuthCode()) ? Hash::compute('sha256',
            $access_token->getAuthCode()) : '';
        $refresh_token_value = !is_null($access_token->getRefreshToken()) ? Hash::compute('sha256',
            $access_token->getRefreshToken()->getValue()) : '';
        $user_id = !is_null($access_token->getUserId()) ? $access_token->getUserId() : 0;

        $this->cache_service->storeHash($hashed_value, [
            'user_id'       => $user_id,
            'client_id'     => $access_token->getClientId(),
            'scope'         => $access_token->getScope(),
            'auth_code'     => $auth_code,
            'issued'        => $access_token->getIssued(),
            'lifetime'      => $access_token->getLifetime(),
            'audience'      => $access_token->getAudience(),
            'from_ip'       => IPHelper::getUserIp(),
            'refresh_token' => $refresh_token_value
        ], intval($access_token->getLifetime()));
    }

    /**
     * @param AccessTokenDB $access_token
     * @throws InvalidAccessTokenException
     */
    public function storeAccessTokenDBOnCache(AccessTokenDB $access_token)
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
        $client  = $access_token->client()->first();

        $this->cache_service->storeHash($access_token->value, [
                'user_id'       => $user_id,
                'client_id'     => $client->client_id,
                'scope'         => $access_token->scope,
                'auth_code'     => $access_token->associated_authorization_code,
                'issued'        => $access_token->created_at,
                'lifetime'      => $access_token->lifetime,
                'from_ip'       => $access_token->from_ip,
                'audience'      => $access_token->audience,
                'refresh_token' => $refresh_token_value
            ], intval($access_token->lifetime));

    }

    /**
     * @param $value
     * @param bool $is_hashed
     * @return AccessToken
     * @throws InvalidAccessTokenException
     * @throws Exception
     */
    public function getAccessToken($value, $is_hashed = false)
    {
        $cache_service           = $this->cache_service;
        $lock_manager_service    = $this->lock_manager_service;
        $configuration_service   = $this->configuration_service;
        $access_token_repository = $this->access_token_repository;

        return $this->tx_service->transaction(function () use (
            $value,
            $is_hashed,
            $cache_service,
            $lock_manager_service,
            $access_token_repository,
            $configuration_service
        ) {
            //hash the given value, bc tokens values are stored hashed on DB
            $hashed_value = !$is_hashed ? Hash::compute('sha256', $value) : $value;
            $access_token = null;

            try
            {
                // check cache ...
                if (!$cache_service->exists($hashed_value))
                {
                    $lock_manager_service->lock('lock.get.accesstoken.' . $hashed_value, function() use($value, $hashed_value, $access_token_repository){
                        // check on DB...
                        $access_token_db = $access_token_repository->getByValue($hashed_value);
                        if (is_null($access_token_db))
                        {
                            if($this->isAccessTokenRevoked($hashed_value))
                            {
                                throw new RevokedAccessTokenException(sprintf('Access token %s is revoked!', $value));
                            }
                            else if($this->isAccessTokenVoid($hashed_value)) // check if its marked on cache as expired ...
                            {
                                throw new ExpiredAccessTokenException(sprintf('Access token %s is expired!', $value));
                            }
                            else
                            {
                                throw new InvalidGrantTypeException(sprintf("Access token %s is invalid!", $value));
                            }
                        }

                        if ($access_token_db->isVoid())
                        {
                            // invalid one ...
                            throw new ExpiredAccessTokenException(sprintf('Access token %s is expired!', $value));
                        }
                        //reload on cache
                        $this->storeAccessTokenDBOnCache($access_token_db);
                    });
                }

                $cache_values = $cache_service->getHash($hashed_value, array
                (
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
                    $access_type               = OAuth2Protocol::OAuth2Protocol_AccessType_Online,
                    $approval_prompt           = OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto,
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
                    $refresh_token = $this->getRefreshToken($refresh_token_value, true);
                    $access_token->setRefreshToken($refresh_token);
                }
            }
            catch (UnacquiredLockException $ex1)
            {
                throw new InvalidAccessTokenException(sprintf("access token %s ", $value));
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
        return ResourceServer::where('ips','like', '%'.$current_ip.'%')
            ->where('active', '=', true)
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

        $client_repository        = $this->client_repository;
        $auth_service             = $this->auth_service;
        $cache_service            = $this->cache_service;
        $access_token_repository  = $this->access_token_repository;
        $refresh_token_repository = $this->refresh_token_repository;

        return $this->tx_service->transaction(function () use (
            $refresh_token,
            $access_token,
            $client_repository,
            $auth_service,
            $cache_service,
            $access_token_repository,
            $refresh_token_repository
        ) {
            $value        = $refresh_token->getValue();
            //hash the given value, bc tokens values are stored hashed on DB
            $hashed_value = Hash::compute('sha256', $value);
            $client_id    = $refresh_token->getClientId();
            $user_id      = $refresh_token->getUserId();
            $client       = $client_repository->getClientById($client_id);
            $user         = $auth_service->getUserById($user_id);

            // todo: move to a factory
            $refresh_token_db = new RefreshTokenDB (
                [
                    'value'    => $hashed_value,
                    'lifetime' => $refresh_token->getLifetime(),
                    'scope'    => $refresh_token->getScope(),
                    'from_ip'  => IPHelper::getUserIp(),
                    'audience' => $access_token->getAudience(),
                ]
            );

            $refresh_token_db->client()->associate($client);
            $refresh_token_db->user()->associate($user);
            $refresh_token_repository->add($refresh_token_db);
            //associate current access token to refresh token on DB
            $access_token_db = AccessTokenDB::where('value', '=', Hash::compute('sha256', $access_token->getValue()))->first();
            $access_token_db->refresh_token()->associate($refresh_token_db);

            $access_token_repository->add($access_token_db);

            $access_token->setRefreshToken($refresh_token);

            $cache_service->incCounter
            (
                $client_id . TokenService::ClientRefreshTokensQty,
                TokenService::ClientRefreshTokensQtyLifetime
            );

            return $refresh_token;
        });

    }

    /**
     * @param \oauth2\services\refresh $value
     * @param bool $is_hashed
     * @return RefreshToken
     * @throws InvalidGrantTypeException
     * @throws ReplayAttackException
     * @throws RevokedRefreshTokenException
     */
    public function getRefreshToken($value, $is_hashed = false)
    {
        //hash the given value, bc tokens values are stored hashed on DB
        $hashed_value     = !$is_hashed ? Hash::compute('sha256', $value) : $value;

        $refresh_token_db = $this->refresh_token_repository->getByValue($hashed_value);

        if (is_null($refresh_token_db))
        {
            if($this->isRefreshTokenRevoked($hashed_value))
                throw new RevokedRefreshTokenException(sprintf("revoked refresh token %s !", $value));

            throw new InvalidGrantTypeException(sprintf("refresh token %s does not exists!", $value));
        }

        if ($refresh_token_db->void)
        {
            throw new ReplayAttackRefreshTokenException
            (
                $value,
                sprintf
                (
                    "refresh token %s is void",
                    $value
                )
            );
        }

        //check is refresh token is stills alive... (ZERO is infinite lifetime)
        if ($refresh_token_db->isVoid())
        {
            throw new InvalidGrantTypeException(sprintf("refresh token %s is expired!", $value));
        }

        $client = $refresh_token_db->client()->first();

        $refresh_token = RefreshToken::load
        (
            array
            (
                'value'     => $value,
                'scope'     => $refresh_token_db->scope,
                'client_id' => $client->client_id,
                'user_id'   => $refresh_token_db->user_id,
                'audience'  => $refresh_token_db->audience,
                'from_ip'   => $refresh_token_db->from_ip,
                'issued'    => $refresh_token_db->created_at,
                'is_hashed' => $is_hashed
            ),
            intval($refresh_token_db->lifetime)
        );

        return $refresh_token;
    }

    /**
     * Revokes all related tokens to a specific auth code
     * @param $auth_code Authorization Code
     * @return mixed
     */
    public function revokeAuthCodeRelatedTokens($auth_code)
    {
        $auth_code_hashed_value   = Hash::compute('sha256', $auth_code);
        $cache_service            = $this->cache_service;
        $access_token_repository  = $this->access_token_repository;
        $refresh_token_repository = $this->refresh_token_repository;

        $this->tx_service->transaction(function () use
        (
            $auth_code_hashed_value,
            $cache_service,
            $access_token_repository,
            $refresh_token_repository
        ) {
            //get related access tokens
            $db_access_token    = $access_token_repository->getByAuthCode($auth_code_hashed_value);
            if(is_null($db_access_token)) return;

            $client             = $db_access_token->client()->first();
            $access_token_value = $db_access_token->value;
            $refresh_token_db   = $db_access_token->refresh_token()->first();
            //remove auth code from client list on cache
            $cache_service->deleteMemberSet
            (
                $client->client_id . TokenService::ClientAuthCodePrefixList,
                $auth_code_hashed_value
            );
                //remove access token from client list on cache
            $cache_service->deleteMemberSet
            (
                $client->client_id . TokenService::ClientAccessTokenPrefixList,
                $access_token_value
            );

            $cache_service->delete($access_token_value);

            $access_token_repository->delete($db_access_token);

            if (!is_null($refresh_token_db)) {
                $this->revokeRefreshToken($refresh_token_db->value, true);
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

        $cache_service            = $this->cache_service;
        $access_token_repository  = $this->access_token_repository;

        return $this->tx_service->transaction(function () use
        (
            $value,
            $is_hashed,
            $cache_service,
            $access_token_repository
        ) {

            //hash the given value, bc tokens values are stored hashed on DB
            $hashed_value = !$is_hashed ? Hash::compute('sha256', $value) : $value;

            $access_token_db = $access_token_repository->getByValue($hashed_value);

            if(is_null($access_token_db)) return false;

            $client          = $access_token_db->client()->first();
            //delete from cache
            $cache_service->delete($hashed_value);
            $cache_service->deleteMemberSet
            (
                $client->client_id . TokenService::ClientAccessTokenPrefixList,
                $access_token_db->value
            );

            //check on DB... and delete it
            $access_token_repository->delete($access_token_db);

            $this->markAccessTokenAsRevoked($hashed_value);

            return true;
        });

    }

    /**
     * @param $value
     * @param bool|false $is_hashed
     * @return bool
     */
    public function expireAccessToken($value, $is_hashed = false)
    {
        $cache_service            = $this->cache_service;
        $access_token_repository  = $this->access_token_repository;

        return $this->tx_service->transaction(function () use
        (
            $value,
            $is_hashed,
            $cache_service,
            $access_token_repository
        ) {
            //hash the given value, bc tokens values are stored hashed on DB
            $hashed_value = !$is_hashed ? Hash::compute('sha256', $value) : $value;

            $access_token_db = $access_token_repository->getByValue($hashed_value);

            if(is_null($access_token_db)) return false;

            $client          = $access_token_db->client()->first();
            //delete from cache
            $cache_service->delete($hashed_value);
            $cache_service->deleteMemberSet
            (
                $client->client_id . TokenService::ClientAccessTokenPrefixList,
                $access_token_db->value
            );

            $access_token_repository->delete($access_token_db);

            $this->markAccessTokenAsVoid($hashed_value);

            return true;
        });
    }

    /**
     * Revokes all related tokens to a specific client id
     * @param $client_id
     */
    public function revokeClientRelatedTokens($client_id)
    {
        //get client auth codes
        $auth_codes     = $this->cache_service->getSet($client_id . self::ClientAuthCodePrefixList);
        //get client access tokens
        $access_tokens  = $this->cache_service->getSet($client_id . self::ClientAccessTokenPrefixList);

        $client_repository = $this->client_repository;
        $cache_service  = $this->cache_service;


        $this->tx_service->transaction(function () use (
            $client_id,
            $auth_codes,
            $access_tokens,
            $cache_service,
            $client_repository

        ) {
            $client = $client_repository->getClientById($client_id);

            if (is_null($client))
            {
                return;
            }
            //revoke on cache
            $cache_service->deleteArray($auth_codes);
            $cache_service->deleteArray($access_tokens);
            //revoke on db
            foreach($client->access_tokens()->get() as $at)
            {
                $this->markAccessTokenAsRevoked($at->value);
            }

            foreach($client->refresh_tokens()->get() as $rt)
            {
                $this->markRefreshTokenAsRevoked($rt);
            }

            $client->access_tokens()->delete();
            $client->refresh_tokens()->delete();
            //delete client list (auth codes and access tokens)
            $cache_service->delete($client_id . TokenService::ClientAuthCodePrefixList);
            $cache_service->delete($client_id . TokenService::ClientAccessTokenPrefixList);
        });
    }

    /**
     * @param string $at_hash
     */
    public function markAccessTokenAsRevoked($at_hash)
    {
        $this->cache_service->addSingleValue
        (
            'access.token:revoked:'.$at_hash,
            'access.token:revoked:'.$at_hash,
            $this->configuration_service->getConfigValue('OAuth2.AccessToken.Revoked.Lifetime')
        );
    }

    /**
     * @param string $at_hash
     */
    public function markAccessTokenAsVoid($at_hash)
    {
        $this->cache_service->addSingleValue
        (
            'access.token:void:'.$at_hash,
            'access.token:void:'.$at_hash,
            $this->configuration_service->getConfigValue('OAuth2.AccessToken.Void.Lifetime')
        );
    }

    /**
     * @param string $rt_hash
     */
    public function markRefreshTokenAsRevoked($rt_hash)
    {
        $this->cache_service->addSingleValue
        (
            'refresh.token:revoked:'.$rt_hash,
            'refresh.token:revoked:'.$rt_hash,
            $this->configuration_service->getConfigValue('OAuth2.RefreshToken.Revoked.Lifetime')
        );
    }

    /**
     * @param string $at_hash
     * @return bool
     */
    public function isAccessTokenRevoked($at_hash)
    {
        return $this->cache_service->exists('access.token:revoked:' . $at_hash);
    }

    /**
     * @param string $at_hash
     * @return bool
     */
    public function isAccessTokenVoid($at_hash)
    {
        return $this->cache_service->exists('access.token:void:' . $at_hash);
    }

    /**
     * @param string $rt_hash
     * @return bool
     */
    public function isRefreshTokenRevoked($rt_hash)
    {
        return $this->cache_service->exists('refresh.token:revoked:' . $rt_hash);
    }

    /**
     * Mark a given refresh token as void
     * @param $value
     * @param bool $is_hashed
     * @return bool
     */
    public function invalidateRefreshToken($value, $is_hashed = false)
    {
        $hashed_value  = !$is_hashed ? Hash::compute('sha256', $value) : $value;
        $refresh_token = $this->refresh_token_repository->getByValue($hashed_value);
        if(is_null($refresh_token)) return false;
        $refresh_token->setVoid();
        $this->refresh_token_repository->add($refresh_token);
        return true;
    }

    /**
     * Revokes a give refresh token and all related access tokens
     * @param $value
     * @param bool $is_hashed
     * @return mixed
     */
    public function revokeRefreshToken($value, $is_hashed = false)
    {
        return $this->tx_service->transaction(function () use ($value, $is_hashed) {
            $res = $this->invalidateRefreshToken($value, $is_hashed);
            return $res && $this->clearAccessTokensForRefreshToken($value, $is_hashed);
        });

    }

    /**
     * Revokes all access tokens for a give refresh token
     * @param string $value refresh token value
     * @param bool $is_hashed
     * @return bool|void
     */
    public function clearAccessTokensForRefreshToken($value, $is_hashed = false)
    {

        $hashed_value             = !$is_hashed ? Hash::compute('sha256', $value) : $value;
        $cache_service            = $this->cache_service;
        $access_token_repository  = $this->access_token_repository;
        $refresh_token_repository = $this->refresh_token_repository;

        return $this->tx_service->transaction(function () use
        (
            $hashed_value,
            $cache_service,
            $access_token_repository, $refresh_token_repository) {

            $refresh_token_db = $refresh_token_repository->getByValue($hashed_value);

            if (!is_null($refresh_token_db))
            {
                $access_tokens_db = $access_token_repository->getByRefreshToken($refresh_token_db->id);

                if (count($access_tokens_db) == 0) return false;

                foreach ($access_tokens_db as $access_token_db)
                {

                    $cache_service->delete($access_token_db->value);
                    $client = $access_token_db->client()->first();
                    $cache_service->deleteMemberSet
                    (
                        $client->client_id . TokenService::ClientAccessTokenPrefixList,
                        $access_token_db->value
                    );

                    $this->markAccessTokenAsRevoked($access_token_db->value);


                    $this->access_token_repository->delete($access_token_db);
                }
            }

            return true;
        });
    }

    /**
     * @param string $nonce
     * @param string $client_id
     * @param AccessToken|null $access_token
     * @param AuthorizationCode|null $auth_code
     * @return IBasicJWT
     * @throws AbsentClientException
     * @throws AbsentCurrentUserException
     * @throws ConfigurationException
     * @throws InvalidClientCredentials
     * @throws \jwt\exceptions\ClaimAlreadyExistsException
     */
    public function createIdToken
    (
        $nonce,
        $client_id,
        AccessToken $access_token    = null,
        AuthorizationCode $auth_code = null
    )
    {
        $issuer    = $this->configuration_service->getSiteUrl();
        if(empty($issuer)) throw new ConfigurationException('missing idp url');

        $client            = $this->client_repository->getClientById($client_id);
        $id_token_lifetime = $this->configuration_service->getConfigValue('OAuth2.IdToken.Lifetime');

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

        $user = $this->auth_service->getUserById
        (
            $this->principal_service->get()->getUserId()
        );

        if(!$user)
            throw new AbsentCurrentUserException;

        // build claim set
        $epoch_now   = time();
        $session_id  = Crypt::encrypt(Session::getId());
        $encoder     = new Base64UrlRepresentation();
        $jti         = $encoder->encode(hash('sha512', $session_id.$client_id, true));

        $this->cache_service->addSingleValue($jti, $session_id, $id_token_lifetime);

        $claim_set = new JWTClaimSet
        (
            $iss = new StringOrURI($issuer),
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
            $exp = new NumericDate($epoch_now + $id_token_lifetime),
            $jti = new JsonValue($jti)
        );

        if(!empty($nonce))
            $claim_set->addClaim(new JWTClaim(OAuth2Protocol::OAuth2Protocol_Nonce, new StringOrURI($nonce)));

        $id_token_response_info = $client->getIdTokenResponseInfo();
        $sig_alg                = $id_token_response_info->getSigningAlgorithm();
        $enc_alg                = $id_token_response_info->getEncryptionKeyAlgorithm();
        $enc                    = $id_token_response_info->getEncryptionContentAlgorithm();

        if(!is_null($sig_alg) && !is_null($access_token))
            $this->buildAccessTokenHashClaim($access_token, $sig_alg , $claim_set);

        if(!is_null($sig_alg) && !is_null($auth_code))
            $this->buildAuthCodeHashClaim($auth_code, $sig_alg , $claim_set);

        $this->buildAuthTimeClaim($claim_set);

        return $this->id_token_builder->buildJWT($claim_set, $id_token_response_info, $client);
    }

    /**
     * @param AccessToken $access_token
     * @param HashFunctionAlgorithm $hashing_alg
     * @param JWTClaimSet $claim_set
     * @return JWTClaimSet
     * @throws InvalidClientCredentials
     * @throws \jwt\exceptions\ClaimAlreadyExistsException
     */
    private function buildAccessTokenHashClaim
    (
        AccessToken $access_token,
        HashFunctionAlgorithm $hashing_alg,
        JWTClaimSet $claim_set
    )
    {
        $at                     = $access_token->getValue();
        $at_len                 = $hashing_alg->getHashKeyLen() / 2 ;
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
                            hash
                            (
                                $hashing_alg->getHashingAlgorithm(),
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

    /**
     * @param AuthorizationCode $auth_code
     * @param HashFunctionAlgorithm $hashing_alg
     * @param JWTClaimSet $claim_set
     * @return JWTClaimSet
     * @throws InvalidClientCredentials
     * @throws \jwt\exceptions\ClaimAlreadyExistsException
     */
    private function buildAuthCodeHashClaim
    (
        AuthorizationCode $auth_code,
        HashFunctionAlgorithm $hashing_alg,
        JWTClaimSet $claim_set
    )
    {

        $ac                     = $auth_code->getValue();
        $ac_len                 = $hashing_alg->getHashKeyLen() / 2 ;
        $encoder                = new Base64UrlRepresentation();

        if($ac_len > ByteUtil::bitLength(strlen($ac)))
            throw new InvalidClientCredentials('invalid auth code length!.');

        $claim_set->addClaim
        (
            new JWTClaim
            (
                OAuth2Protocol::OAuth2Protocol_AuthCode_Hash,
                new JsonValue
                (
                    $encoder->encode
                    (
                        substr
                        (
                            hash
                            (
                                $hashing_alg->getHashingAlgorithm(),
                                $ac,
                                true
                            ),
                            0,
                            $ac_len / 8
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

    /**
     * @param AuthorizationCode $auth_code
     * @return AccessToken|null
     */
    public function getAccessTokenByAuthCode(AuthorizationCode $auth_code)
    {
        $auth_code_value = Hash::compute('sha256', $auth_code->getValue());
        $db_access_token = $this->access_token_repository->getByAuthCode($auth_code_value);
        if(is_null($db_access_token)) return null;
        return $this->getAccessToken($db_access_token->value, true);
    }

}