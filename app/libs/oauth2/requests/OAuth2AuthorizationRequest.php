<?php

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

    public function getResponseType(){
        return $this[OAuth2Protocol::OAuth2Protocol_ResponseType];
    }

    public function getClientId(){
        return $this[OAuth2Protocol::OAuth2Protocol_ClientId];
    }

    public function getRedirectUri(){
        return $this[OAuth2Protocol::OAuth2Protocol_RedirectUri];
    }

    public function getScope(){
        return $this[OAuth2Protocol::OAuth2Protocol_Scope];
    }


    public function getState(){
        return (isset($this[OAuth2Protocol::OAuth2Protocol_State]))? $this[OAuth2Protocol::OAuth2Protocol_State]:null;
    }

    public function isValid()
    {
        if(!isset($this[OAuth2Protocol::OAuth2Protocol_ResponseType]))
            return false;

        if(!isset($this[OAuth2Protocol::OAuth2Protocol_ClientId]))
            return false;

        if(!isset($this[OAuth2Protocol::OAuth2Protocol_RedirectUri]))
            return false;

        if(!isset($this[OAuth2Protocol::OAuth2Protocol_Scope]))
            return false;

        return true;
    }
}
