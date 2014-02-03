<?php

namespace oauth2\services;

use oauth2\models\AuthorizationCode;
use oauth2\models\AccessToken;
use oauth2\models\RefreshToken;
use oauth2\OAuth2Protocol;

/**
 * Interface ITokenService
 * Defines the interface for an OAuth2 Token Service
 * Provides all Tokens related operations (create, get and revoke)
 * @package oauth2\services
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
     * @return AuthorizationCode
     */
    public function createAuthorizationCode($user_id, $client_id, $scope, $audience='' , $redirect_uri = null,$access_type = OAuth2Protocol::OAuth2Protocol_AccessType_Online,$approval_prompt = OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto, $has_previous_user_consent=false);


    /**
     * Retrieves a given Authorization Code
     * @param $value
     * @return AuthorizationCode
     * @throws \oauth2\exceptions\ReplayAttackException
     * @throws \oauth2\exceptions\InvalidAuthorizationCodeException
     */
    public function getAuthorizationCode($value);

    /** Given an Authorization code, creates a brand new Access Token
     * @param $auth_code AuthorizationCode
     * @param null $redirect_uri
     * @return AccessToken
     */
    public function createAccessToken(AuthorizationCode $auth_code,$redirect_uri=null);


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
     * @throws \oauth2\exceptions\InvalidAccessTokenException
     * @throws \oauth2\exceptions\InvalidGrantTypeException
     */
    public function getAccessToken($value, $is_hashed = false);

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
     * @param  $value refresh token value
     * @param $is_hashed
     * @return RefreshToken
     * @throws \oauth2\exceptions\ReplayAttackException
     * @throws \oauth2\exceptions\InvalidGrantTypeException
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

    public function getRefreshTokeByUserId($user_id);

    /**
     * Revokes a given access token
     * @param $value
     * @param bool $is_hashed
     * @return bool
     */
    public function revokeAccessToken($value, $is_hashed = false);

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

} 