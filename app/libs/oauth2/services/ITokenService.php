<?php

namespace oauth2\services;

use oauth2\models\AuthorizationCode;
use oauth2\models\AccessToken;
use oauth2\models\RefreshToken;

/**
 * Interface ITokenService
 * @package oauth2\services
 */
interface ITokenService {

    /** Creates a brand new authorization code
     * @param $client_id
     * @param $scope
     * @param string $audience
     * @param null $redirect_uri
     * @return mixed
     */
    public function createAuthorizationCode($client_id, $scope, $audience='' , $redirect_uri = null);


    /**
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
     * @param $scope
     * @param $client_id
     * @param $audience
     * @return mixed
     */
    public function createAccessTokenFromParams($scope, $client_id, $audience);


    /** Creates a new Access Token from a given refresh token, and invalidate former associated
     *  Access Token
     * @param RefreshToken $refresh_token
     * @param null $scope
     * @return mixed
     */
    public function createAccessTokenFromRefreshToken(RefreshToken $refresh_token, $scope=null);

    /**
     * @param $value
     * @return AccessToken
     */
    public function getAccessToken($value);

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


    /**
     * Revokes a given access token and optionally , its associated refresh token
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