<?php

namespace oauth2\models;

use Zend\Math\Rand;
use services\IPHelper;

/**
 * Class RefreshToken
 * http://tools.ietf.org/html/rfc6749#section-1.5
 *
 * The refresh token is also a secret bound to the client identifier and
 * client instance that originally requested the authorization; the
 * refresh token also represents the original resource owner grant.
 * This is ensured by the authorization process as follows:
 * 1.  The resource owner and user agent safely deliver the
 * authorization "code" to the client instance in the first place.
 * 2.  The client uses it immediately in secure transport-level
 * communications to the authorization server and then securely
 * stores the long-lived refresh token.
 * 3.  The client always uses the refresh token in secure transport-
 * level communications to the authorization server to get an access
 * token (and optionally roll over the refresh token).
 * So, as long as the confidentiality of the particular token can be
 * ensured by the client, a refresh token can also be used as an
 * alternative means to authenticate the client instance itself.
 * from http://tools.ietf.org/html/rfc6819#section-3.3
 * @package oauth2\models
 */
class RefreshToken extends Token {

    public function __construct(){
        parent::__construct(Token::DefaultByteLength);
    }

    public static function create(AccessToken $access_token, $lifetime = 0){
        $instance = new self();
        $instance->value        = Rand::getString($instance->len,null,true);
        $instance->scope        = $access_token->getScope();
        $instance->client_id    = $access_token->getClientId();
        $instance->audience     = $access_token->getAudience();
        $instance->from_ip      = IPHelper::getUserIp();
        $instance->lifetime     = $lifetime;
        return $instance;
    }

    public static function load(array $params, $lifetime = 0){
        $instance = new self();
        $instance->value        = $params['value'];
        $instance->scope        = $params['scope'];
        $instance->client_id    = $params['client_id'];
        $instance->audience     = $params['audience'];
        $instance->from_ip      = $params['from_ip'];
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