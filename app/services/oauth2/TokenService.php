<?php

namespace services\oauth2;

use AccessToken as DBAccessToken;
use oauth2\exceptions\InvalidAccessTokenException;
use oauth2\exceptions\InvalidAuthorizationCodeException;
use oauth2\exceptions\ReplayAttackException;
use oauth2\models\AccessToken;
use oauth2\models\AuthorizationCode;
use oauth2\models\RefreshToken;
use oauth2\models\Token;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use RefreshToken as DBRefreshToken;
use services\IPHelper;
use Zend\Crypt\Hash;
use utils\exceptions\UnacquiredLockException;
use utils\services\ILockManagerService;

/**
 * Class TokenService
 * @package services\oauth2
 */

class TokenService implements ITokenService
{


    private $redis;
    private $client_service;
    private $lock_manager_service;

    public function __construct(IClientService $client_service, ILockManagerService $lock_manager_service)
    {
        $this->redis = \RedisLV4::connection();
        $this->client_service = $client_service;
        $this->lock_manager_service = $lock_manager_service;
    }

    /**
     * @param $client_id
     * @param null $redirect_uri
     * @return Token
     */
    public function createAuthorizationCode($client_id, $scope, $redirect_uri = null)
    {
        $code = AuthorizationCode::create($client_id, $scope, $redirect_uri);
        //stores in REDIS

        $value = $code->getValue();
        $hashed_value = Hash::compute('sha256', $value);

        $this->redis->hmset($hashed_value, array(
            'value' => $hashed_value,
            'client_id' => $code->getClientId(),
            'scope' => $code->getScope(),
            'redirect_uri' => $code->getRedirectUri(),
            'issued' => $code->getIssued(),
            'lifetime' => $code->getLifetime(),
        ));

        $this->redis->expire($hashed_value, $code->getLifetime());

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

        $hashed_value = Hash::compute('sha256', $value);
        if (!$this->redis->exists($hashed_value))
            throw new InvalidAuthorizationCodeException("auth_code %s ", $value);

        try {
            $this->lock_manager_service->acquireLock('lock.get.authcode.' . $hashed_value);

            $values = $this->redis->hmget($hashed_value, array(
                "value",
                "client_id",
                "scope",
                "redirect_uri",
                "issued",
                "lifetime"
            ));

            $code = AuthorizationCode::load($values[0], $values[1], $values[2], $values[3], $values[4], $values[5]);
            return $code;
        } catch (UnacquiredLockException $ex1) {
            throw new ReplayAttackException("auth_code %s ", $value);
        }
    }

    /**
     * @param $auth_code
     * @param $client_id
     * @param $scope
     * @param null $redirect_uri
     * @return Token
     */
    public function createAccessToken(AuthorizationCode $auth_code, $redirect_uri = null)
    {
        $access_token = AccessToken::create($auth_code);
        $value = $access_token->getValue();
        $hashed_value = Hash::compute('sha256', $value);

        $this->storesAccessTokenOnRedis($access_token);

        $client_id = $access_token->getClientId();
        $client = $this->client_service->getClientById($client_id);

        //stores in DB
        $access_token_db = new DBAccessToken;
        $access_token_db->value = $hashed_value;
        $access_token_db->from_ip = IPHelper::getUserIp();
        $access_token_db->associated_authorization_code = $access_token->getAuthCode();
        $access_token_db->lifetime = $access_token->getLifetime();
        $access_token_db->scope = $access_token->getScope();
        $access_token_db->client_id = $client->getId();
        $access_token_db->audience = $access_token->getAudience();
        $access_token_db->Save();

        return $access_token;
    }

    /**
     * @param AccessToken $access_token
     * @throws \oauth2\exceptions\InvalidAccessTokenException
     */
    private function storesAccessTokenOnRedis(AccessToken $access_token)
    {
        //stores in REDIS

        $value = $access_token->getValue();
        $hashed_value = Hash::compute('sha256', $value);

        if ($this->redis->exists($hashed_value))
            throw new InvalidAccessTokenException;

        $this->redis->hmset($hashed_value, array(
            'value' => $hashed_value,
            'client_id' => $access_token->getClientId(),
            'scope' => $access_token->getScope(),
            'auth_code' => $access_token->getAuthCode(),
            'issued' => $access_token->getIssued(),
            'lifetime' => $access_token->getLifetime(),
            'audience' => $access_token->getAudience(),
            'from_ip' => IPHelper::getUserIp()
        ));

        $this->redis->expire($hashed_value, $access_token->getLifetime());
    }

    /**
     * @param $value
     * @throws \oauth2\exceptions\InvalidAccessTokenException
     */
    public function getAccessToken($value)
    {

        $hashed_value = Hash::compute('sha256', $value);

        try {
            if (!$this->redis->exists($hashed_value)) {
                //check on DB...
                $access_token_db = DBAccessToken::where('value', '=', $hashed_value)->first();
                if (is_null($access_token_db))
                    throw new InvalidAccessTokenException("access token %s ", $value);
                //lock ...
                $lock_name = 'lock.get.accesstoken.' . $hashed_value;
                $this->lock_manager_service->acquireLock($lock_name);

                $lifetime = $access_token_db->lifetime;
                $created_at = new DateTime($access_token_db->created_at);
                $created_at->add(new DateInterval('PT' . $lifetime . 'S'));
                $now = new DateTime(gmdate("Y-m-d H:i:s", time()));
                //check validity...
                if ($now > $created_at) {
                    //invalid one ...
                    $access_token_db->delete();
                    throw new InvalidAccessTokenException;
                }
                //reload on redis
                $this->storesDBAccessTokenOnRedis($access_token_db);
                //release lock
                $this->lock_manager_service->releaseLock($lock_name);
            }

            $values = $this->redis->hmget($hashed_value, array(
                'value',
                'client_id',
                'scope',
                'auth_code',
                'issued',
                'lifetime',
                'from_ip',
                'audience'
            ));

            $code = AuthorizationCode::load($values[3], $values[1], $values[2]);
            $access_token = AccessToken::load($values[0], $code, $values[4], $values[5], $values[6], $values[7]);
        } catch (UnacquiredLockException $ex1) {
            throw new InvalidAccessTokenException("access token %s ", $value);
        }
        return $access_token;
    }

    /**
     * @param DBAccessToken $access_token
     * @throws \oauth2\exceptions\InvalidAccessTokenException
     */
    private function storesDBAccessTokenOnRedis(DBAccessToken $access_token)
    {
        //stores in REDIS

        if ($this->redis->exists($access_token->value))
            throw new InvalidAccessTokenException;

        $this->redis->hmset($access_token->value, array(
            'value' => $access_token->value,
            'client_id' => $access_token->client_id,
            'scope' => $access_token->scope,
            'auth_code' => $access_token->associated_authorization_code,
            'issued' => $access_token->created_at,
            'lifetime' => $access_token->lifetime,
            'from_ip' => $access_token->from_ip,
            'audience' => $access_token->audience,
        ));

        $this->redis->expire($access_token->value, $access_token->lifetime);
    }

    public function revokeAccessToken($value)
    {
        $hashed_value = Hash::compute('sha256', $value);
        if ($this->redis->exists($hashed_value)) {
            $this->redis->del($hashed_value);
        }
        //check on DB...
        $access_token_db = DBAccessToken::where('value', '=', $hashed_value)->first();
        if (!is_null($access_token_db))
            $access_token_db->delete();
    }

    /**
     * @param $access_token
     * @return RefreshToken
     */
    public function createRefreshToken(AccessToken $access_token)
    {
        $refresh_token = RefreshToken::create($access_token);
        $value = $refresh_token->getValue();
        $hashed_value = Hash::compute('sha256', $value);
        $client_id = $refresh_token->getClientId();
        $client = $this->client_service->getClientById($client_id);
        //stores in DB
        $refresh_token_db = new DBRefreshToken;
        $refresh_token_db->value = $hashed_value;
        $refresh_token_db->associated_access_token = $refresh_token->getAccessToken();
        $refresh_token_db->lifetime = $refresh_token->getLifetime();
        $refresh_token_db->scope = $refresh_token->getScope();
        $refresh_token_db->client_id = $client->getId();
        $refresh_token_db->from_ip = IPHelper::getUserIp();
        $refresh_token_db->Save();

        return $refresh_token;
    }

    public function getRefreshToken($value)
    {

    }

    public function getRevokeToken($value)
    {

    }
}

