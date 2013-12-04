<?php


namespace services\oauth2;

use oauth2\models\AuthorizationCode;
use oauth2\models\Token;
use oauth2\services\ITokenService;

/**
 * Class TokenService
 * @package services\oauth2
 */

class TokenService implements ITokenService{


    private $redis;

    public function __construct(){
        $this->redis = \RedisLV4::connection();
    }

    /**
     * @param $client_id
     * @param null $redirect_uri
     * @return Token
     */
    public function getAuthorizationCode($client_id, $redirect_uri = null)
    {
        $code = new AuthorizationCode($client_id,$redirect_uri);
        $this->redis->setex($code->getValue(), $code->getLifetime(),$code->toJSON());
        return $code;
    }

    /**
     * @param $auth_code
     * @param $client_id
     * @param $scope
     * @param null $redirect_uri
     * @return Token
     */
    public function getAccessToken($auth_code, $client_id, $scope, $redirect_uri = null)
    {
        // TODO: Implement getAccessToken() method.
    }

    /**
     * @param $client_id
     * @param $scope
     * @return Token
     */
    public function getRefreshToken($client_id, $scope)
    {
        // TODO: Implement getRefreshToken() method.
    }
}