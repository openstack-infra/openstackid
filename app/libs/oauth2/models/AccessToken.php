<?php

namespace oauth2\models;

use Zend\Math\Rand;

/**
 * Class AccessToken
 * http://tools.ietf.org/html/rfc6749#section-1.4
 * @package oauth2\models
 */
class AccessToken extends Token {

    private $auth_code;

    public function __construct(){
        parent::__construct(Token::DefaultByteLength);
    }

    public static function create(AuthorizationCode $auth_code,  $lifetime = 3600){
        $instance = new self();
        $instance->value        = Rand::getString($instance->len,null,true);
        $instance->scope        = $auth_code->getScope();
        $instance->client_id    = $auth_code->getClientId();
        $instance->auth_code    = $auth_code->getValue();
        $instance->audience     = $auth_code->getAudience();
        $instance->lifetime     = $lifetime;
        return $instance;
    }

    public static function createFromRefreshToken(RefreshToken $refresh_token,$scope = null,  $lifetime = 3600){
        $instance = new self();
        $instance->value        = Rand::getString($instance->len,null,true);
        $instance->scope        = $scope;
        $instance->from_ip      = $refresh_token->getFromIp();
        $instance->client_id    = $refresh_token->getClientId();
        $instance->auth_code    = null;
        $instance->audience     = $refresh_token->getAudience();
        $instance->lifetime     = $lifetime;
        return $instance;
    }

    public static function load($value, AuthorizationCode $auth_code, $issued=null, $lifetime = 3600){
        $instance = new self();
        $instance->value        = $value;
        $instance->scope        = $auth_code->getScope();
        $instance->client_id    = $auth_code->getClientId();
        $instance->auth_code    = $auth_code->getValue();
        $instance->audience     = $auth_code->getAudience();
        $instance->from_ip      = $auth_code->getFromIp();
        $instance->issued       = $issued;
        $instance->lifetime     = $lifetime;
        return $instance;
    }

    public function getAuthCode(){
        return $this->auth_code;
    }


    public function toJSON(){
        return '{}';
    }

    public function fromJSON($json){

    }
} 