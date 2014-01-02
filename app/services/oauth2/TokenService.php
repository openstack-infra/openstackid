<?php

namespace services\oauth2;

use AccessToken as DBAccessToken;
use DB;
use oauth2\exceptions\InvalidAccessTokenException;
use oauth2\exceptions\InvalidAuthorizationCodeException;
use oauth2\exceptions\InvalidGrantTypeException;
use oauth2\exceptions\ReplayAttackException;
use oauth2\models\AccessToken;
use oauth2\models\AuthorizationCode;
use oauth2\models\RefreshToken;
use oauth2\models\Token;
use oauth2\services\Authorization;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;

use RefreshToken as RefreshTokenDB;
use RefreshToken as DBRefreshToken;

use services\IPHelper;
use utils\exceptions\UnacquiredLockException;

use utils\services\ILockManagerService;
use utils\services\IServerConfigurationService;
use Zend\Crypt\Hash;

use DateInterval;
use DateTime;

/**
 * Class TokenService
 * @package services\oauth2
 */

class TokenService implements ITokenService
{
    const ClientAccessTokenPrefixList = '.atokens';
    const ClientAuthCodePrefixList = '.acodes';
    private $redis;
    private $client_service;
    private $lock_manager_service;
    private $configuration_service;

    public function __construct(IClientService $client_service, ILockManagerService $lock_manager_service, IServerConfigurationService $configuration_service)
    {
        $this->redis                 = \RedisLV4::connection();
        $this->client_service        = $client_service;
        $this->lock_manager_service  = $lock_manager_service;
        $this->configuration_service = $configuration_service;
    }

    /**
     * Creates a brand new Authorization Code
     * @param $client_id
     * @param $scope
     * @param string $audience
     * @param null $redirect_uri
     * @return AuthorizationCode
     */
    public function createAuthorizationCode($client_id, $scope, $audience = '', $redirect_uri = null)
    {
        //create model
        $code = AuthorizationCode::create($client_id, $scope, $audience, $redirect_uri, $this->configuration_service->getConfigValue('OAuth2.AuthorizationCode.Lifetime'));

        $value = $code->getValue();
        $hashed_value = Hash::compute('sha256', $value);
        //stores in REDIS
        $this->redis->hmset($hashed_value, array(
            'value'        => $hashed_value,
            'client_id'    => $code->getClientId(),
            'scope'        => $code->getScope(),
            'redirect_uri' => $code->getRedirectUri(),
            'issued'       => $code->getIssued(),
            'lifetime'     => $code->getLifetime(),
            'audience'     => $code->getAudience()
        ));
        //sets expiration time
        $this->redis->expire($hashed_value, $code->getLifetime());

        //stores brand new auth code hash value on a set by client id...
        $this->redis->sadd($client_id . self::ClientAuthCodePrefixList, $hashed_value);

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
            throw new InvalidAuthorizationCodeException(sprintf("auth_code %s ", $value));

        try {

            $this->lock_manager_service->acquireLock('lock.get.authcode.' . $hashed_value);

            $values = $this->redis->hmget($hashed_value, array(
                'value',
                'client_id',
                'scope',
                'redirect_uri',
                'issued',
                'lifetime',
                'audience'
            ));

            $code = AuthorizationCode::load($values[0], $values[1], $values[2], $values[6], $values[3], $values[4], $values[5]);
            return $code;
        } catch (UnacquiredLockException $ex1) {
            throw new ReplayAttackException($value, sprintf("auth_code %s ", $value));
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
        $access_token = AccessToken::create($auth_code, $this->configuration_service->getConfigValue('OAuth2.AccessToken.Lifetime'));
        $value = $access_token->getValue();
        $hashed_value = Hash::compute('sha256', $value);

        $this->storesAccessTokenOnRedis($access_token);

        $client_id = $access_token->getClientId();
        $client    = $this->client_service->getClientById($client_id);

        //stores in DB
        $access_token_db                                = new DBAccessToken;
        $access_token_db->value                         = $hashed_value;
        $access_token_db->from_ip                       = IPHelper::getUserIp();
        $access_token_db->associated_authorization_code = Hash::compute('sha256', $access_token->getAuthCode());
        $access_token_db->lifetime                      = $access_token->getLifetime();
        $access_token_db->scope                         = $access_token->getScope();
        $access_token_db->client_id                     = $client->getId();
        $access_token_db->audience                      = $access_token->getAudience();
        $access_token_db->Save();

        //stores brand new access token hash value on a set by client id...
        $this->redis->sadd($client_id . self::ClientAccessTokenPrefixList, $hashed_value);
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
            'value'     => $hashed_value,
            'client_id' => $access_token->getClientId(),
            'scope'     => $access_token->getScope(),
            'auth_code' => Hash::compute('sha256', $access_token->getAuthCode()),
            'issued'    => $access_token->getIssued(),
            'lifetime'  => $access_token->getLifetime(),
            'audience'  => $access_token->getAudience(),
            'from_ip'   => IPHelper::getUserIp()
        ));

        $this->redis->expire($hashed_value, $access_token->getLifetime());
    }

    /**
     * @param RefreshToken $refresh_token
     * @param null $scope
     * @return AccessToken|void
     */
    public function createAccessTokenFromRefreshToken(RefreshToken $refresh_token, $scope = null)
    {

        $access_token = null;
        //preserve entire operation on db transaction...
        DB::transaction(function () use ($refresh_token, $scope, &$access_token) {

            $refresh_token_value        = $refresh_token->getValue();
            $refresh_token_hashed_value = Hash::compute('sha256', $refresh_token_value);
            //set current access token as invalid
            $original_access_token      = $refresh_token->getAccessToken();
            $this->revokeAccessToken($original_access_token, true);
            //validate scope if present...
            if (!is_null($scope) && empty($scope)) {
                $original_scope     = $refresh_token->getScope();
                $aux_original_scope = explode(' ', $original_scope);
                $aux_scope = explode(' ', $scope);
                //compare original scope with given one, and validate if its included on original one
                //or not
                if (count(array_diff($aux_scope, $aux_original_scope)) !== 0)
                    throw new InvalidGrantTypeException(sprintf("requested scope %s is not contained on original one %s", $scope, $original_scope));
            } else {
                //get original scope
                $scope = $refresh_token->getScope();
            }

            //create new access token
            $access_token = AccessToken::createFromRefreshToken($refresh_token, $scope, $this->configuration_service->getConfigValue('OAuth2.AccessToken.Lifetime'));
            $value        = $access_token->getValue();
            $hashed_value = Hash::compute('sha256', $value);

            $this->storesAccessTokenOnRedis($access_token);

            //get current client
            $client_id = $access_token->getClientId();
            $client    = $this->client_service->getClientById($client_id);

            //stores in DB
            $access_token_db                                = new DBAccessToken;
            $access_token_db->value                         = $hashed_value;
            $access_token_db->from_ip                       = IPHelper::getUserIp();
            $access_token_db->associated_authorization_code = $access_token->getAuthCode();
            $access_token_db->lifetime                      = $access_token->getLifetime();
            $access_token_db->scope                         = $access_token->getScope();
            $access_token_db->client_id                     = $client->getId();
            $access_token_db->audience                      = $access_token->getAudience();
            $access_token_db->Save();

            //update current refresh token
            RefreshTokenDB::where('value', '=', $refresh_token_hashed_value)->update(array('associated_access_token' => $hashed_value));
            //stores brand new access token hash value on a set by client id...
            $this->redis->sadd($client_id . self::ClientAccessTokenPrefixList, $hashed_value);

        });
        return $access_token;
    }

    /**
     * @param $value
     * @return AccessToken
     * @throws \oauth2\exceptions\InvalidAccessTokenException
     * @throws \oauth2\exceptions\InvalidGrantTypeException
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

                $lifetime   = $access_token_db->lifetime;
                $created_at = $access_token_db->created_at;
                $created_at->add(new DateInterval('PT' . $lifetime . 'S'));
                $now = new DateTime(gmdate("Y-m-d H:i:s", time()));
                //check validity...
                if ($now > $created_at) {
                    //invalid one ...
                    $access_token_db->delete();
                    throw new InvalidGrantTypeException(sprintf('Access token %s is expired!', $value));
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

            $code = AuthorizationCode::load($values[3], $values[1], $values[2], $values[7]);
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
            'value'      => $access_token->value,
            'client_id'  => $access_token->client_id,
            'scope'      => $access_token->scope,
            'auth_code'  => $access_token->associated_authorization_code,
            'issued'     => $access_token->created_at,
            'lifetime'   => $access_token->lifetime,
            'from_ip'    => $access_token->from_ip,
            'audience'   => $access_token->audience,
        ));

        $this->redis->expire($access_token->value, $access_token->lifetime);
    }

    public function revokeAccessToken($value, $already_hashed = false)
    {
        //hash the given value, bc tokens values are stored hashed on DB
        $hashed_value = !$already_hashed?Hash::compute('sha256', $value):$value;
        //delete from redis
        if ($this->redis->exists($hashed_value)) {
            $this->redis->del($hashed_value);
        }
        //check on DB... and delete it
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
        $refresh_token = RefreshToken::create($access_token,$this->configuration_service->getConfigValue('OAuth2.RefreshToken.Lifetime'));
        $value = $refresh_token->getValue();
        //hash the given value, bc tokens values are stored hashed on DB
        $hashed_value = Hash::compute('sha256', $value);

        $client_id = $refresh_token->getClientId();
        $client    = $this->client_service->getClientById($client_id);
        //stores in DB
        $refresh_token_db                          = new DBRefreshToken;
        $refresh_token_db->value                   = $hashed_value;
        $refresh_token_db->associated_access_token = Hash::compute('sha256', $refresh_token->getAccessToken());
        $refresh_token_db->lifetime                = $refresh_token->getLifetime();
        $refresh_token_db->scope                   = $refresh_token->getScope();
        //stored client identifier to preserve db relationship (FK)
        $refresh_token_db->client_id               = $client->getId();
        $refresh_token_db->from_ip                 = IPHelper::getUserIp();
        $refresh_token_db->audience                = $access_token->getAudience();
        $refresh_token_db->Save();

        return $refresh_token;
    }

    /**
     * Get a refresh token by its value
     * @param  $value refresh token value
     * @return RefreshToken
     * @throws \oauth2\exceptions\ReplayAttackException
     * @throws \oauth2\exceptions\InvalidGrantTypeException
     */
    public function getRefreshToken($value)
    {
        //hash the given value, bc tokens values are stored hashed on DB
        $hashed_value = Hash::compute('sha256', $value);

        $refresh_token_db = DBRefreshToken::where('value', '=', $hashed_value)->first();

        if (is_null($refresh_token_db))
            throw new InvalidGrantTypeException(sprintf("refresh token %s does not exists!", $value));

        if ($refresh_token_db->void) {
            throw new ReplayAttackException($value, sprintf("refresh token %s is void", $value));
        }

        //check is refresh token is stills alive... (ZERO is infinite lifetime)
        if ($refresh_token_db->lifetime !== 0) {
            $created_at = $refresh_token_db->created_at;
            $created_at->add(new DateInterval('PT' . $refresh_token_db->lifetime . 'S'));
            $now = new DateTime(gmdate("Y-m-d H:i:s", time()));
            //check validity...
            if ($now > $created_at)
                throw new InvalidGrantTypeException(sprintf("refresh token %s does is expired!", $value));
        }

        $client        = $this->client_service->getClientByIdentifier($refresh_token_db->client_id);
        $auth_code     = AuthorizationCode::load(null, $client->getClientId(), $refresh_token_db->scope, $refresh_token_db->audience, null, null, 600, $refresh_token_db->from_ip);
        $access_token  = AccessToken::Load($refresh_token_db->associated_access_token, $auth_code);
        $refresh_token = RefreshToken::load($value, $access_token, $refresh_token_db->lifetime);

        return $refresh_token;
    }

    /**
     * Revokes all related tokens to a specific auth code
     * @param $auth_code Authorization Code
     * @return mixed
     */
    public function revokeAuthCodeRelatedTokens($auth_code)
    {
        $auth_code_hashed_value = Hash::compute('sha256', $auth_code);

        DB::transaction(function () use ($auth_code_hashed_value) {
            $db_access_tokens = DBAccessToken::where('associated_authorization_code', '=', $auth_code_hashed_value)->get();
            foreach ($db_access_tokens as $db_access_token) {
                $access_token_value = $db_access_token->value;
                DBRefreshToken::where('associated_access_token', '=', $access_token_value)->delete();
                $this->redis->del($access_token_value);
                $db_access_token->delete();
            }
        });
    }

    /**
     * Revokes all related tokens to a specific client id
     * @param $client_id
     */
    public function revokeClientRelatedTokens($client_id)
    {
        //get client auth codes
        $auth_codes = $this->redis->smembers($client_id . self::ClientAuthCodePrefixList);
        //get client access tokens
        $access_tokens = $this->redis->smembers($client_id . self::ClientAccessTokenPrefixList);

        DB::transaction(function () use ($client_id, $auth_codes, $access_tokens) {

            foreach ($auth_codes as $auth_code) {
                $this->redis->del($auth_code);
            }

            foreach ($access_tokens as $access_token) {
                DBAccessToken::where('value', '=', $access_token)->delete();
                DBRefreshToken::where('associated_access_token', '=', $access_token)->delete();
                $this->redis->del($access_token);
            }
            //delete client list (auth codes and access tokens)
            $this->redis->del($client_id . self::ClientAuthCodePrefixList);
            $this->redis->del($client_id . self::ClientAccessTokenPrefixList);
        });
    }

    /**
     * Mark a given refresh token as void
     * @param $value
     * @return mixed|void
     */
    public function invalidateRefreshToken($value)
    {
        $hashed_value = Hash::compute('sha256', $value);
        RefreshTokenDB::where('value', '=', $hashed_value)->update(array('void' => true));
    }

    /**
     * Checks if current_ip has access rights on the given $access_token
     * @param AccessToken $access_token
     * @param $current_ip
     * @return bool
     */
    public function checkAccessTokenAudience(AccessToken $access_token, $current_ip){
        $current_audience = $access_token->getAudience();
        $current_audience = explode(' ',$current_audience);
        if(!is_array($current_audience))
            $current_audience = array($current_audience);

        return \ResourceServer
             ::where('active','=',true)
            ->where('ip','=',$current_ip)
            ->whereIn('host',$current_audience)->count() > 0;
    }

}

