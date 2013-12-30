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
    /**
     * @param $client_id
     * @param $scope
     * @param null $redirect_uri
     * @return AuthorizationCode
     */
    public function createAuthorizationCode($client_id, $scope, $redirect_uri = null);


    /**
     * @param $value
     * @return AuthorizationCode
     * @throws \oauth2\exceptions\ReplayAttackException
     * @throws \oauth2\exceptions\InvalidAuthorizationCodeException
     */
    public function getAuthorizationCode($value);

    /**
     * @param $auth_code AuthorizationCode
     * @param null $redirect_uri
     * @return AccessToken
     */
    public function createAccessToken(AuthorizationCode $auth_code,$redirect_uri=null);


    /**
     * @param RefreshToken $refresh_token
     * @return AccessToken
     */
    public function createAccessTokenFromRefreshToken(RefreshToken $refresh_token, $scope=null);

    /**
     * @param $value
     * @return AccessToken
     */
    public function getAccessToken($value);

    public function revokeAccessToken($value);

    /**
     * @param $access_token
     * @return RefreshToken
     */
    public function createRefreshToken(AccessToken $access_token);

    /**
     * @param $value refresh token value
     * @return RefreshToken
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
} 