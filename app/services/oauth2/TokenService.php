<?php


namespace services\oauth2;

use oauth2\models\AccessToken;
use oauth2\models\AuthorizationCode;
use oauth2\models\RefreshToken;
use oauth2\services\IClientService;
use \RefreshToken as DBRefreshToken;
use oauth2\models\Token;
use oauth2\services\ITokenService;
use oauth2\exceptions\InvalidAuthorizationCodeException;
use oauth2\exceptions\ReplayAttackException;
use oauth2\exceptions\InvalidAccessTokenException;

/**
 * Class TokenService
 * @package services\oauth2
 */

class TokenService implements ITokenService {


    private $redis;
    private $client_service;

    public function __construct(IClientService $client_service){
        $this->redis = \RedisLV4::connection();
        $this->client_service = $client_service;
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
            'client_id'    => $code->getClientId(),
            'scope'        => $code->getScope(),
            'redirect_uri' => $code->getRedirectUri(),
            'issued'       => $code->getIssued(),
            'lifetime'     => $code->getLifetime(),
        ));

        $this->redis->expire($code->getValue(), $code->getLifetime());

        return $code;
    }

    /**
     * @param $value
     * @return AuthorizationCode
     * @throws \oauth2\exceptions\ReplayAttackException
     * @throws \oauth2\exceptions\InvalidAuthorizationCodeException
     */
    public function getAuthorizationCode($value)
    {
        if(!$this->redis->exists($value))
            throw new InvalidAuthorizationCodeException;

        $success = $this->redis->setnx('lock.get.authcode.'.$value , 1);
        if (!$success) { // only one time we could use this handle
            throw new ReplayAttackException;
        }

        $values = $this->redis->hmget($value, array(
            "value",
            "client_id",
            "scope",
            "redirect_uri",
            "issued",
            "lifetime"
            ));

        $code = AuthorizationCode::load($values[0],$values[1],$values[2],$values[3],$values[4],$values[5]);
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
        //stores in REDIS
        $this->redis->hmset($access_token->getValue(), array(
            'value'        => $access_token->getValue(),
            'redirect_uri' => $access_token->getRedirectUri(),
            'client_id'    => $access_token->getClientId(),
            'scope'        => $access_token->getScope(),
            'auth_code'    => $access_token->getAuthCode(),
            'issued'       => $access_token->getIssued(),
            'lifetime'     => $access_token->getLifetime(),
        ));

        $this->redis->expire($access_token->getValue(), $access_token->getLifetime());

        return $access_token;
    }

    /**
     * @param $value
     * @throws \oauth2\exceptions\InvalidAccessTokenException
     */
    public function getAccessToken($value)
    {

        if(!$this->redis->exists($value))
            throw new InvalidAccessTokenException;

        $values = $this->redis->hmget($value, array(
            "value",
            "redirect_uri",
            "client_id",
            "scope",
            "auth_code",
            "issued",
            "lifetime",
        ));
        $code         = AuthorizationCode::load($values[4],$values[2],$values[3]);
        $access_token = AccessToken::load($values[0].$code,$values[5],$values[1],$values[6]);

        return $access_token;
    }


    /**
     * @param $access_token
     * @return RefreshToken
     */
    public function createRefreshToken(AccessToken $access_token)
    {
        $refresh_token = RefreshToken::create($access_token);
        $client_id     = $refresh_token->getClientId();
        $client        = $this->client_service->getClientById($client_id);
        //stores in DB
        $refresh_token_db                          = new DBRefreshToken;
        $refresh_token_db->value                   = $refresh_token->getValue();
        $refresh_token_db->associated_access_token = $refresh_token->getAccessToken();
        $refresh_token_db->lifetime                = $refresh_token->getLifetime();
        $refresh_token_db->scope                   = $refresh_token->getScope();
        $refresh_token_db->client_id               = $client->getId();

        $refresh_token_db->Save();

        return $refresh_token;
    }


    public function getRefreshToken($value)
    {
        // TODO: Implement getRefreshToken() method.
    }
}