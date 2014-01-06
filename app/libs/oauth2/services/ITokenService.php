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
     * @param $value
     * @param bool $already_hashed
     * @return mixed
     */
    public function revokeAccessToken($value,$already_hashed = false);

    /**
     * @param $access_token
     * @return RefreshToken
     */
    public function createRefreshToken(AccessToken $access_token);

    /**
     * @param  $value refresh token value
     * @return RefreshToken
     * @throws \oauth2\exceptions\ReplayAttackException
     * @throws \oauth2\exceptions\InvalidGrantTypeException
     */
    public function getRefreshToken($value);


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
     * Marks refresh token as void
     * @param $value
     * @return mixed
     */
    public function invalidateRefreshToken($value);


    /**
     * Checks if current_ip has access rights on the given $access_token
     * @param AccessToken $access_token
     * @param $current_ip
     * @return bool
     */
    public function checkAccessTokenAudience(AccessToken $access_token, $current_ip);
} 