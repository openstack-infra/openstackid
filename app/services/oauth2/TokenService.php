<?php


namespace services\oauth2;

use oauth2\models\AccessToken;
use oauth2\models\AuthorizationCode;
use oauth2\models\RefreshToken;
use oauth2\models\Token;
use oauth2\services\ITokenService;
use oauth2\exceptions\InvalidAuthorizationCodeException;
use oauth2\exceptions\ReplayAttackException;

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
    public function createAuthorizationCode($client_id, $scope, $redirect_uri = null)
    {
        $code = AuthorizationCode::create($client_id,$scope,$redirect_uri);
        //stores in REDIS
        $this->redis->hmset($code->getValue(), array(
            'value'        => $code->getValue(),
            'redirect_uri' => $code->getRedirectUri(),
            'client_id'    => $code->getClientId(),
            'scope'        => $code->getScope(),
        ));
        $this->redis->expire($code->getValue(), $code->getLifetime());

        return $code;
    }

    /**
     * @param $value
     * @return AuthorizationCode
     */
    public function getAuthorizationCode($value)
    {
        if(!$this->redis->exists($value))
            throw new InvalidAuthorizationCodeException;

        $success = $this->redis->setnx('lock.get.authcode.'.$value . 1);
        if (!$success) { // only one time we could use this handle
            throw new ReplayAttackException;
        }

        $values = $this->redis->hmget($value, array(
            "value",
            "redirect_uri",
            "client_id",
            "scope",
            ));

        $code = AuthorizationCode::load($values[0],$values[2],$values[3],$values[1]);

        return $code;
    }


    /**
     * @param $auth_code
     * @param $client_id
     * @param $scope
     * @param null $redirect_uri
     * @return Token
     */
    public function createAccessToken(AuthorizationCode $auth_code,$redirect_uri=null)
    {
       $access_token = AccessToken::create($auth_code,$redirect_uri);
       return $access_token;
    }


    /**
     * @param $access_token
     * @return RefreshToken
     */
    public function createRefreshToken(AccessToken $access_token)
    {
        // TODO: Implement createRefreshToken() method.
    }
}