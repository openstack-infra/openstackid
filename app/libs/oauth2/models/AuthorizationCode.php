<?php

namespace oauth2\models;

use services\IPHelper;
use Zend\Math\Rand;
use oauth2\OAuth2Protocol;
/**
 * Class AuthorizationCode
 * http://tools.ietf.org/html/rfc6749#section-1.3.1
 * @package oauth2\models
 */
class AuthorizationCode extends Token {

    private $redirect_uri;


    public function __construct(){
        parent::__construct(64);
    }

    /**
     * @param $client_id
     * @param $scope
     * @param $redirect_uri
     * @param int $lifetime
     * @return AuthorizationCode
     */
    public static function create($client_id, $scope, $audience='' ,$redirect_uri = null, $lifetime = 600){
        $instance = new self();
        $instance->value        = Rand::getString($instance->len, OAuth2Protocol::VsChar, true);
        $instance->scope        = $scope;
        $instance->redirect_uri = $redirect_uri;
        $instance->client_id    = $client_id;
        $instance->lifetime     = $lifetime;
        $instance->audience     = $audience;
        $instance->is_hashed    = false;
        $instance->from_ip      = IPHelper::getUserIp();
        return $instance;
    }

    public static function load($value, $client_id, $scope,$audience='', $redirect_uri = null, $issued = null, $lifetime = 600, $from_ip = '127.0.0.1',$is_hashed = false){
        $instance = new self();
        $instance->value        = $value;
        $instance->scope        = $scope;
        $instance->redirect_uri = $redirect_uri;
        $instance->client_id    = $client_id;
        $instance->audience     = $audience;
        $instance->issued       = $issued;
        $instance->lifetime     = $lifetime;
        $instance->from_ip      = $from_ip;
        $instance->is_hashed    = $is_hashed;
        return $instance;
    }


    public function getRedirectUri(){
        return $this->redirect_uri;
    }

    public function toJSON()
    {
        $o = array(
            'value'        => $this->value,
            'redirect_uri' => $this->redirect_uri,
            'client_id'    => $this->client_id,
            'scope'        => $this->scope,
        );

        return json_encode($o);
    }

    public function fromJSON($json)
    {
        $o = json_decode($json);

        $this->value     = $o->value;
        $this->scope     = $o->scope;
        $this->client_id = $o->client_id;
        $this->scope     = $o->redirect_uri;
    }
}