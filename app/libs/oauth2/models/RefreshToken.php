<?php

namespace oauth2\models;

use Zend\Math\Rand;

/**
 * Class RefreshToken
 * http://tools.ietf.org/html/rfc6749#section-1.5
 * @package oauth2\models
 */
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
        $instance->audience     = $access_token->getAudience();
        $instance->from_ip      = $instance->getFromIp();
        $instance->lifetime     = $lifetime;
        return $instance;
    }

    public static function load($value ,AccessToken $access_token, $lifetime = 0){
        $instance = new self();
        $instance->value        = $value;
        $instance->scope        = $access_token->getScope();
        $instance->client_id    = $access_token->getClientId();
        $instance->access_token = $access_token->getValue();
        $instance->audience     = $access_token->getAudience();
        $instance->from_ip      = $access_token->getFromIp();
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