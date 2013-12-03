<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/3/13
 * Time: 6:11 PM
 */

namespace oauth2\services;


use oauth2\models\Token;

interface ITokenService {
    /**
     * @param $client_id
     * @param null $redirect_uri
     * @return Token
     */
    public function getAuthorizationCode($client_id,$redirect_uri=null);

    /**
     * @param $auth_code
     * @param $client_id
     * @param $scope
     * @param null $redirect_uri
     * @return Token
     */
    public function getAccessToken($auth_code,$client_id,$scope,$redirect_uri=null);

    /**
     * @param $client_id
     * @param $scope
     * @return Token
     */
    public function getRefreshToken($client_id,$scope);
} 