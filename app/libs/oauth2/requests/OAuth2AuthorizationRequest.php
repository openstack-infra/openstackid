<?php

namespace oauth2\requests;

use oauth2\OAuth2Protocol;
use oauth2\OAuth2Message;

/**
 * Class OAuth2AuthorizationRequest
 * http://tools.ietf.org/html/rfc6749#section-4.1.1
 * @package oauth2\requests
 */
class OAuth2AuthorizationRequest extends OAuth2Request {

    public function __construct(OAuth2Message $msg)
    {
        parent::__construct($msg);
    }

    public static $params = array(
        OAuth2Protocol::OAuth2Protocol_ResponseType => OAuth2Protocol::OAuth2Protocol_ResponseType,
        OAuth2Protocol::OAuth2Protocol_ClientId     => OAuth2Protocol::OAuth2Protocol_ClientId,
        OAuth2Protocol::OAuth2Protocol_RedirectUri  => OAuth2Protocol::OAuth2Protocol_RedirectUri,
        OAuth2Protocol::OAuth2Protocol_Scope        => OAuth2Protocol::OAuth2Protocol_Scope,
        OAuth2Protocol::OAuth2Protocol_State        => OAuth2Protocol::OAuth2Protocol_State
    );

    public function getResponseType(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_ResponseType);
    }

    public function getClientId(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_ClientId);
    }

    public function getRedirectUri(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_RedirectUri);
    }

    public function getScope(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_Scope);
    }

    public function getState(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_State);
    }

    public function isValid()
    {
        if(is_null($this->getResponseType()))
            return false;

        if(is_null($this->getClientId()))
            return false;

        if(is_null($this->getRedirectUri()))
            return false;

        return true;
    }
}
