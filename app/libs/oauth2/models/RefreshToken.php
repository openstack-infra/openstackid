<?php

namespace oauth2\models;

use Zend\Math\Rand;

class RefreshToken extends Token {

    private $access_token;

    public function __construct(){
        parent::__construct(Token::DefaultByteLength);
    }

    public function getAccessToken(){
        return $this->access_token;
    }

    public static function create(AccessToken $access_token, $lifetime = 0){
        $instance = new self();
        $instance->value        = Rand::getString($instance->len,null,true);
        $instance->scope        = $access_token->getScope();
        $instance->client_id    = $access_token->getClientId();
        $instance->access_token = $access_token->getValue();
        $instance->lifetime     = $lifetime;
        return $instance;
    }

    public static function load($value ,AccessToken $access_token, $lifetime = 0){
        $instance = new self();
        $instance->value        = $value;
        $instance->scope        = $access_token->getScope();
        $instance->client_id    = $access_token->getClientId();
        $instance->access_token = $access_token->getValue();
        $instance->lifetime     = $lifetime;
        return $instance;
    }

    public function toJSON()
    {
        // TODO: Implement toJSON() method.
    }

    public function fromJSON($json)
    {
        // TODO: Implement fromJSON() method.
    }
}