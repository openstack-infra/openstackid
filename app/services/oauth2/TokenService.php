<?php

namespace services\oauth2;

use AccessToken as DBAccessToken;
use DB;
use oauth2\exceptions\InvalidAccessTokenException;
use oauth2\exceptions\InvalidAuthorizationCodeException;
use oauth2\exceptions\InvalidGrantTypeException;
use oauth2\exceptions\ReplayAttackException;

use oauth2\models\AccessToken;
use oauth2\models\IClient;
use oauth2\models\AuthorizationCode;
use oauth2\models\RefreshToken;
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
 * Provides all Tokens related operations (create, get and revoke)
 * @package services\oauth2
 */

class TokenService implements ITokenService
{
    const ClientAccessTokenPrefixList = '.atokens';
    const ClientAuthCodePrefixList    = '.acodes';

    const ClientAuthCodeQty          = '.acodes.qty';
    const ClientAuthCodeQtyLifetime  = 86400;

    const ClientAccessTokensQty      = '.atokens.qty';
    const ClientAccessTokensQtyLifetime = 86400;

    const ClientRefreshTokensQty = '.rtokens.qty';
    const ClientRefreshTokensQtyLifetime = 86400;


    //services
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
     * @param $scope required scope
     * @param string $audience aimed resource server audience
     * @param null $redirect_uri registered client redirect uri
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

        if($this->redis->setnx($client_id . self::ClientAuthCodeQty,1)){
            $this->redis->expire($client_id . self::ClientAuthCodeQty, self::ClientAuthCodeQtyLifetime);
        }
        else{
            $this->redis->incr($client_id . self::ClientAuthCodeQty);
        }


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
     * Creates a brand new access token from a give auth code
     * @param AuthorizationCode $auth_code
     * @param null $redirect_uri
     * @return AccessToken
     */
    public function createAccessToken(AuthorizationCode $auth_code, $redirect_uri = null)
    {
        $access_token = AccessToken::create($auth_code, $this->configuration_service->getConfigValue('OAuth2.AccessToken.Lifetime'));

        DB::transaction(function () use ($auth_code, $redirect_uri, &$access_token) {
            $value        = $access_token->getValue();
            $hashed_value = Hash::compute('sha256', $value);

            $client_id = $access_token->getClientId();
            $client    = $this->client_service->getClientById($client_id);

            $access_token_db = new DBAccessToken (
                array(
                    'value'                         => $hashed_value,
                    'from_ip'                       => IPHelper::getUserIp(),
                    'associated_authorization_code' => Hash::compute('sha256', $access_token->getAuthCode()),
                    'lifetime'                      => $access_token->getLifetime(),
                    'scope'                         => $access_token->getScope(),
                    'audience'                      => $access_token->getAudience()
                )
            );
            $access_token_db->client()->associate($client);
            $access_token_db->save();
            //check if use refresh tokens...
            if($client->use_refresh_token && $client->getClientType()==IClient::ClientType_Confidential) {
                $this->createRefreshToken($access_token);
            }

            $this->storesAccessTokenOnRedis($access_token);

            //stores brand new access token hash value on a set by client id...
            $this->redis->sadd($client_id . self::ClientAccessTokenPrefixList, $hashed_value);

            if($this->redis->setnx($client_id . self::ClientAccessTokensQty,1)){
                $this->redis->expire($client_id . self::ClientAccessTokensQty, self::ClientAccessTokensQtyLifetime);
            }
            else{
                $this->redis->incr($client_id . self::ClientAccessTokensQty);
            }

        });

        return $access_token;
    }

    public function createAccessTokenFromParams($scope, $client_id, $audience)
    {
        $access_token = AccessToken::createFromParams($scope, $client_id, $audience, $this->configuration_service->getConfigValue('OAuth2.AccessToken.Lifetime'));
        $value        = $access_token->getValue();
        $hashed_value = Hash::compute('sha256', $value);

        $this->storesAccessTokenOnRedis($access_token);

        $client_id = $access_token->getClientId();
        $client    = $this->client_service->getClientById($client_id);

        //stores in DB
        $access_token_db = new DBAccessToken(
            array(
                'value'    => $hashed_value,
                'from_ip'  => IPHelper::getUserIp(),
                'lifetime' => $access_token->getLifetime(),
                'scope'    => $access_token->getScope(),
                'audience' => $access_token->getAudience()
            )
        );

        $access_token_db->client()->associate($client);
        $access_token_db->Save();



        //stores brand new access token hash value on a set by client id...
        $this->redis->sadd($client_id . self::ClientAccessTokenPrefixList, $hashed_value);

        if($this->redis->setnx($client_id . self::ClientAccessTokensQty,1)){
            $this->redis->expire($client_id . self::ClientAccessTokensQty, self::ClientAccessTokensQtyLifetime);
        }
        else{
            $this->redis->incr($client_id . self::ClientAccessTokensQty);
        }

        return $access_token;
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
            //clear current access tokens as invalid
            $this->clearAccessTokensForRefreshToken($refresh_token->getValue());

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
            $access_token_db = new DBAccessToken(
                array(
                    'value'    => $hashed_value,
                    'from_ip'  => IPHelper::getUserIp(),
                    'lifetime' => $access_token->getLifetime(),
                    'scope'    => $access_token->getScope(),
                    'audience' => $access_token->getAudience()
                )
            );

            //save relationships
            $refresh_token_db = DBRefreshToken::where('value','=',$refresh_token_hashed_value)->first();
            $access_token_db->refresh_token()->associate($refresh_token_db);
            $access_token_db->client()->associate($client);
            $access_token_db->Save();

            //stores brand new access token hash value on a set by client id...
            $this->redis->sadd($client_id . self::ClientAccessTokenPrefixList, $hashed_value);

            if($this->redis->setnx($client_id . self::ClientAccessTokensQty,1)){
                $this->redis->expire($client_id . self::ClientAccessTokensQty, self::ClientAccessTokensQtyLifetime);
            }
            else{
                $this->redis->incr($client_id . self::ClientAccessTokensQty);
            }
        });
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

        $refresh_token_value = !is_null($access_token->getRefreshToken()) ? Hash::compute('sha256', $access_token->getRefreshToken()->getValue()):'';

        $this->redis->hmset($hashed_value, array(
            'value'         => $hashed_value,
            'client_id'     => $access_token->getClientId(),
            'scope'         => $access_token->getScope(),
            'auth_code'     => Hash::compute('sha256', $access_token->getAuthCode()),
            'issued'        => $access_token->getIssued(),
            'lifetime'      => $access_token->getLifetime(),
            'audience'      => $access_token->getAudience(),
            'from_ip'       => IPHelper::getUserIp(),
            'refresh_token' => $refresh_token_value
        ));

        $this->redis->expire($hashed_value, $access_token->getLifetime());
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

        $refresh_token_value = '';
        $refresh_token_db = $access_token->refresh_token()->first();
        if(!is_null($refresh_token_db))
            $refresh_token_value = $refresh_token_db->value;

        $this->redis->hmset($access_token->value, array(
            'value'         => $access_token->value,
            'client_id'     => $access_token->client_id,
            'scope'         => $access_token->scope,
            'auth_code'     => $access_token->associated_authorization_code,
            'issued'        => $access_token->created_at,
            'lifetime'      => $access_token->lifetime,
            'from_ip'       => $access_token->from_ip,
            'audience'      => $access_token->audience,
            'refresh_token' => $refresh_token_value
        ));

        $this->redis->expire($access_token->value, $access_token->lifetime);
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
                    throw new InvalidGrantTypeException(sprintf("Access token %s is invalid!", $value));
                //lock ...
                $lock_name = 'lock.get.accesstoken.' . $hashed_value;
                $this->lock_manager_service->acquireLock($lock_name);

                //check lifetime...
                $lifetime   = $access_token_db->lifetime;
                $created_at = $access_token_db->created_at;
                $created_at->add(new DateInterval('PT' . $lifetime . 'S'));
                $now        = new DateTime(gmdate("Y-m-d H:i:s", time()));
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
                'audience',
                'refresh_token'
            ));

            $code                = AuthorizationCode::load($values[3], $values[1], $values[2], $values[7]);
            $access_token        = AccessToken::load($values[0], $code, $values[4], $values[5], $values[6], $values[7]);
            $refresh_token_value = $values[8];

            if(!empty($refresh_token_value)){
                $refresh_token   = $this->getRefreshToken($refresh_token_value,true);
                $access_token->setRefreshToken($refresh_token);
            }
        } catch (UnacquiredLockException $ex1) {
            throw new InvalidAccessTokenException("access token %s ", $value);
        }
        return $access_token;
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


    /**
     * Creates a new refresh token and associate it with given access token
     * @param AccessToken $access_token
     * @return RefreshToken
     */
    public function createRefreshToken(AccessToken &$access_token)
    {
        $refresh_token = RefreshToken::create($access_token, $this->configuration_service->getConfigValue('OAuth2.RefreshToken.Lifetime'));
        $value         = $refresh_token->getValue();
        //hash the given value, bc tokens values are stored hashed on DB
        $hashed_value = Hash::compute('sha256', $value);

        $client_id = $refresh_token->getClientId();
        $client    = $this->client_service->getClientById($client_id);
        //stores in DB
        $refresh_token_db = new DBRefreshToken (
            array(
                'value'      => $hashed_value,
                'lifetime'   => $refresh_token->getLifetime(),
                'scope'      => $refresh_token->getScope(),
                'from_ip'    => IPHelper::getUserIp(),
                'audience'   => $access_token->getAudience(),
            )
        );

        $refresh_token_db->client()->associate($client);
        $refresh_token_db->Save();
        //associate current access token to refresh token on DB
        $access_token_db = DBAccessToken::where('value','=',Hash::compute('sha256',$access_token->getValue()))->first();
        $access_token_db->refresh_token()->associate($refresh_token_db);
        $access_token_db->Save();

        $access_token->setRefreshToken($refresh_token);

        if($this->redis->setnx($client_id . self::ClientRefreshTokensQty,1)){
            $this->redis->expire($client_id . self::ClientRefreshTokensQty, self::ClientRefreshTokensQtyLifetime);
        }
        else{
            $this->redis->incr($client_id . self::ClientRefreshTokensQty);
        }

        return $refresh_token;
    }

    /**
     * Get a refresh token by its value
     * @param  $value refresh token value
     * @param $is_hashed
     * @return RefreshToken
     * @throws \oauth2\exceptions\ReplayAttackException
     * @throws \oauth2\exceptions\InvalidGrantTypeException
     */
    public function getRefreshToken($value, $is_hashed = false)
    {
        //hash the given value, bc tokens values are stored hashed on DB
        $hashed_value = !$is_hashed ? Hash::compute('sha256', $value):$value;

        $refresh_token_db = DBRefreshToken::where('value', '=', $hashed_value)->first();

        if (is_null($refresh_token_db))
            throw new InvalidGrantTypeException(sprintf("Refresh token %s does not exists!", $value));

        if ($refresh_token_db->void) {
            throw new ReplayAttackException($value, sprintf("Refresh token %s is void", $value));
        }

        //check is refresh token is stills alive... (ZERO is infinite lifetime)
        if ($refresh_token_db->lifetime !== 0) {
            $created_at = $refresh_token_db->created_at;
            $created_at->add(new DateInterval('PT' . $refresh_token_db->lifetime . 'S'));
            $now = new DateTime(gmdate("Y-m-d H:i:s", time()));
            //check validity...
            if ($now > $created_at)
                throw new InvalidGrantTypeException(sprintf("Refresh token %s is expired!", $value));
        }

        $client        = $refresh_token_db->client()->first();

        $refresh_token = RefreshToken::load(array(
            'value'     => $value,
            'scope'     => $refresh_token_db->scope,
            'client_id' => $client->client_id,
            'audience'  => $refresh_token_db->audience,
            'from_ip'   => $refresh_token_db->from_ip,
            'issued'    => $refresh_token_db->created_at
        ), $refresh_token_db->lifetime);

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
                $refresh_token_db   = $db_access_token->refresh_token()->first();
                if(!is_null($refresh_token_db))
                    $refresh_token_db->delete();
                $this->redis->del($access_token_value);
                $db_access_token->delete();
            }
        });
    }

    /**
     * Revokes a given access token
     * @param $value
     * @param bool $is_hashed
     * @return bool
     */
    public function revokeAccessToken($value, $is_hashed = false)
    {

        $res = 0;
        DB::transaction(function () use ($value, $is_hashed, &$res) {
            //hash the given value, bc tokens values are stored hashed on DB
            $hashed_value = !$is_hashed?Hash::compute('sha256', $value):$value;
            //delete from redis
            if ($this->redis->exists($hashed_value)) {
                $res = $this->redis->del($hashed_value);
            }
            //check on DB... and delete it
            $res = DBAccessToken::where('value', '=', $hashed_value)->delete();

        });
        return $res > 0;
    }

    /**
     * Revokes all related tokens to a specific client id
     * @param $client_id
     */
    public function revokeClientRelatedTokens($client_id)
    {
        //get client auth codes
        $auth_codes    = $this->redis->smembers($client_id . self::ClientAuthCodePrefixList);
        //get client access tokens
        $access_tokens = $this->redis->smembers($client_id . self::ClientAccessTokenPrefixList);

        DB::transaction(function () use ($client_id, $auth_codes, $access_tokens) {

            if(count($auth_codes)>0)
                $this->redis->del($auth_codes);

            if(count($access_tokens)>0)
                $this->redis->del($access_tokens);

            DBAccessToken::where('client_id','=',$client_id)->delete();
            DBRefreshToken::where('client_id','=',$client_id)->delete();

            //delete client list (auth codes and access tokens)
            $this->redis->del($client_id . self::ClientAuthCodePrefixList);
            $this->redis->del($client_id . self::ClientAccessTokenPrefixList);
        });
    }


    /**
     * Mark a given refresh token as void
     * @param $value
     * @param bool $is_hashed
     * @param bool $revoke_related_access_token
     * @return mixed
     */
    public function invalidateRefreshToken($value, $is_hashed = false)
    {
        $hashed_value = !$is_hashed?Hash::compute('sha256', $value):$value;
        $res          = RefreshTokenDB::where('value', '=', $hashed_value)->update(array('void' => true));
        return $res > 0;
    }


    /**
     * Revokes a give refresh token and all related access tokens
     * @param $value
     * @param bool $is_hashed
     * @return mixed
     */
    public function revokeRefreshToken($value, $is_hashed = false){
        $res = false;
        DB::transaction(function () use ($value,$is_hashed, &$res) {
            $res  = $this->invalidateRefreshToken($value,$is_hashed);
            $res  = $res && $this->clearAccessTokensForRefreshToken($value,$is_hashed);
        });
        return $res;
    }

    /**
     * Revokes all access tokens for a give refresh token
     * @param $value refresh token value
     * @param bool $is_hashed
     * @return bool|void
     */
    public function clearAccessTokensForRefreshToken($value, $is_hashed = false){

        $hashed_value = !$is_hashed?Hash::compute('sha256', $value):$value;

        DB::transaction(function () use ($hashed_value) {
            $refresh_token_db = DBRefreshToken::where('value','=',$hashed_value)->first();
            if(!is_null($refresh_token_db)){
                $access_tokens_db = DBAccessToken::where('refresh_token_id','=',$refresh_token_db->id)->get();
                foreach($access_tokens_db as $access_token_db){
                    $res = $this->redis->del(array($access_token_db->value));
                    $client = $access_token_db->client()->first();
                    $res = $this->redis->srem($client->client_id . self::ClientAccessTokenPrefixList, $access_token_db->value);
                    $access_token_db->delete();
                }
            }
        });
    }

}

