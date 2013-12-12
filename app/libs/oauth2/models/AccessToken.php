<?php

namespace oauth2\models;

use Zend\Math\Rand;

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

    public static function load($value,AuthorizationCode $auth_code, $issued,$lifetime = 3600, $from_ip='127.0.0.1',$audience=null){
        $instance = new self();
        $instance->value        = $value;
        $instance->scope        = $auth_code->getScope();
        $instance->client_id    = $auth_code->getClientId();
        $instance->auth_code    = $auth_code->getValue();
        $instance->audience     = $auth_code->getAudience();
        $instance->issued       = $issued;
        $instance->lifetime     = $lifetime;
        $instance->from_ip      = $from_ip;
        $instance->audience     = $audience;
        return $instance;
    }

    public function getAuthCode(){
        return $this->auth_code;
    }


    public function toJSON(){
        return '{}';
    }

    public function fromJSON($json)
    {

    }
} 