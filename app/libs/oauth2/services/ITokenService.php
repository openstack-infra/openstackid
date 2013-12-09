<?php

namespace oauth2\services;

use oauth2\models\AuthorizationCode;
use oauth2\models\AccessToken;
use oauth2\models\RefreshToken;

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
     */
    public function getAuthorizationCode($value);

    /**
     * @param $auth_code AuthorizationCode
     * @param null $redirect_uri
     * @return AccessToken
     */
    public function createAccessToken(AuthorizationCode $auth_code,$redirect_uri=null);

    /**
     * @param $access_token
     * @return RefreshToken
     */
    public function createRefreshToken(AccessToken $access_token);
} 