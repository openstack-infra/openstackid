<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/2/13
 * Time: 2:42 PM
 */

namespace oauth2\requests;
use oauth2\OAuth2Protocol;

class OAuth2AuthorizationRequest extends OAuth2Request {

    public function __construct(array $values)
    {
        parent::__construct($values);
    }


    public static $params = array(
        OAuth2Protocol::OAuth2Protocol_ResponseType => OAuth2Protocol::OAuth2Protocol_ResponseType,
        OAuth2Protocol::OAuth2Protocol_ClientId     => OAuth2Protocol::OAuth2Protocol_ClientId,
        OAuth2Protocol::OAuth2Protocol_RedirectUri  => OAuth2Protocol::OAuth2Protocol_RedirectUri,
        OAuth2Protocol::OAuth2Protocol_Scope        => OAuth2Protocol::OAuth2Protocol_Scope,
        OAuth2Protocol::OAuth2Protocol_State        => OAuth2Protocol::OAuth2Protocol_State
    );

    public function isValid()
    {
        // TODO: Implement isValid() method.
    }
}