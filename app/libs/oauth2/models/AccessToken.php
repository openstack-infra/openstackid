<?php

namespace oauth2\models;

use Zend\Math\Rand;
use oauth2\OAuth2Protocol;
/**
 * Class AccessToken
 * http://tools.ietf.org/html/rfc6749#section-1.4
 * @package oauth2\models
 */
class AccessToken extends Token {

    private $auth_code;

    private $refresh_token;

    public function __construct(){
        parent::__construct(72);
    }

    public static function create(AuthorizationCode $auth_code,  $lifetime = 3600){
        $instance = new self();
        $instance->value        = Rand::getString($instance->len, OAuth2Protocol::VsChar, true);
        $instance->scope        = $auth_code->getScope();
        $instance->client_id    = $auth_code->getClientId();
        $instance->auth_code    = $auth_code->getValue();
        $instance->audience     = $auth_code->getAudience();
        $instance->lifetime     = $lifetime;
        $instance->is_hashed    = false;
        return $instance;
    }

    public static function createFromParams($scope, $client_id, $audience,$lifetime){
        $instance = new self();
        $instance->value         = Rand::getString($instance->len,OAuth2Protocol::VsChar,true);
        $instance->scope         = $scope;
        $instance->client_id     = $client_id;
        $instance->auth_code     = null;
        $instance->audience      = $audience;
        $instance->refresh_token = null;
        $instance->lifetime      = $lifetime;
        $instance->is_hashed     = false;
        return $instance;
    }

    public static function createFromRefreshToken(RefreshToken $refresh_token,$scope = null,  $lifetime = 3600){
        $instance = new self();
        $instance->value         = Rand::getString($instance->len,OAuth2Protocol::VsChar,true);
        $instance->scope         = $scope;
        $instance->from_ip       = $refresh_token->getFromIp();
        $instance->client_id     = $refresh_token->getClientId();
        $instance->auth_code     = null;
        $instance->refresh_token = $refresh_token;
        $instance->audience      = $refresh_token->getAudience();
        $instance->lifetime      = $lifetime;
        $instance->is_hashed    =  false;
        return $instance;
    }

    public static function load($value, AuthorizationCode $auth_code, $issued = null, $lifetime = 3600, $is_hashed=false){
        $instance = new self();
        $instance->value        = $value;
        $instance->scope        = $auth_code->getScope();
        $instance->client_id    = $auth_code->getClientId();
        $instance->auth_code    = $auth_code->getValue();
        $instance->audience     = $auth_code->getAudience();
        $instance->from_ip      = $auth_code->getFromIp();
        $instance->issued       = $issued;
        $instance->lifetime     = $lifetime;
        $instance->is_hashed    = $is_hashed;
        return $instance;
    }

    public function getAuthCode(){
        return $this->auth_code;
    }

    public function getRefreshToken(){
        return $this->refresh_token;
    }

    public function setRefreshToken(RefreshToken $refresh_token){
        $this->refresh_token = $refresh_token;;
    }


    public function toJSON(){
        return '{}';
    }

    public function fromJSON($json){

    }
} 