<?php namespace OAuth2\Services;
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
use jwt\IBasicJWT;
use OAuth2\Exceptions\InvalidAuthorizationCodeException;
use OAuth2\Exceptions\ReplayAttackException;
use OAuth2\Models\AuthorizationCode;
use OAuth2\Models\AccessToken;
use OAuth2\Models\RefreshToken;
use OAuth2\OAuth2Protocol;
use OAuth2\Exceptions\InvalidAccessTokenException;
use OAuth2\Exceptions\InvalidGrantTypeException;
/**
 * Interface ITokenService
 * Defines the interface for an OAuth2 Token Service
 * Provides all Tokens related operations (create, get and revoke)
 * @package OAuth2\Services
 */
interface ITokenService {

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
    );


    /**
     * Retrieves a given Authorization Code
     * @param $value
     * @return AuthorizationCode
     * @throws ReplayAttackException
     * @throws InvalidAuthorizationCodeException
     */
    public function getAuthorizationCode($value);

    /** Given an Authorization code, creates a brand new Access Token
     * @param $auth_code AuthorizationCode
     * @param null $redirect_uri
     * @return AccessToken
     */
    public function createAccessToken(AuthorizationCode $auth_code, $redirect_uri=null);

    /**
     * Create a brand new Access Token by params
     * @param $client_id
     * @param $scope
     * @param $audience
     * @param null $user_id
     * @return AccessToken
     */
    public function createAccessTokenFromParams($client_id,$scope, $audience,$user_id=null);


    /** Creates a new Access Token from a given refresh token, and invalidate former associated
     *  Access Token
     * @param RefreshToken $refresh_token
     * @param null $scope
     * @return mixed
     */
    public function createAccessTokenFromRefreshToken(RefreshToken $refresh_token, $scope=null);

    /**
     * Retrieves a given Access Token
     * @param $value
     * @param $is_hashed
     * @return AccessToken
     * @throws InvalidAccessTokenException
     * @throws InvalidGrantTypeException
     */
    public function getAccessToken($value, $is_hashed = false);

    /**
     * @param AuthorizationCode $auth_code
     * @return AccessToken|null
     */
    public function getAccessTokenByAuthCode(AuthorizationCode $auth_code);

    /**
     * Checks if current_ip has access rights on the given $access_token
     * @param AccessToken $access_token
     * @param $current_ip
     * @return bool
     */
    public function checkAccessTokenAudience(AccessToken $access_token, $current_ip);


    /**
     * Creates a new refresh token and associate it with given access token
     * @param AccessToken $access_token
     * @return RefreshToken
     */
    public function createRefreshToken(AccessToken &$access_token);

    /**
     * Get a refresh token by its value
     * @param string $value
     * @param bool $is_hashed
     * @return RefreshToken
     * @throws ReplayAttackException
     * @throws InvalidGrantTypeException
     */
    public function getRefreshToken($value, $is_hashed = false);


    /**
     * Revokes all related tokens to a specific auth code
     * @param $auth_code Authorization Code
     * @return mixed
     */
    public function revokeAuthCodeRelatedTokens($auth_code);

    /**
     * Revokes all related tokens to a specific client id
     * @param $client_id
     */
    public function revokeClientRelatedTokens($client_id);

    public function getAccessTokenByClient($client_id);
    
    public function getRefreshTokenByClient($client_id);

    public function getAccessTokenByUserId($user_id);

    public function getRefreshTokenByUserId($user_id);

    /**
     * Revokes a given access token
     * @param $value
     * @param bool $is_hashed
     * @return bool
     */
    public function revokeAccessToken($value, $is_hashed = false);

    /**
     * @param $value
     * @param bool|false $is_hashed
     * @return bool
     */
    public function expireAccessToken($value, $is_hashed = false);

    /**
     * @param $value refresh_token value
     * @param bool $is_hashed
     * @return bool
     */
    public function clearAccessTokensForRefreshToken($value,$is_hashed = false);

    /**
     * Mark a given refresh token as void
     * @param $value
     * @param bool $is_hashed
     * @return bool
     */
    public function invalidateRefreshToken($value, $is_hashed = false);


    /**
     * Revokes a give refresh token and all related access tokens
     * @param $value
     * @param bool $is_hashed
     * @return mixed
     */
    public function revokeRefreshToken($value, $is_hashed = false);


    /**
     * @param string $nonce
     * @param string $client_id
     * @param AccessToken|null $access_token
     * @param AuthorizationCode $auth_code
     * @return IBasicJWT
     */
    public function createIdToken
    (
        $nonce,
        $client_id,
        AccessToken $access_token    = null,
        AuthorizationCode $auth_code = null
    );

} 