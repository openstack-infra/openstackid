<?php

namespace oauth2\requests;

use oauth2\OAuth2Protocol;

class OAuth2AccessTokenRequest  extends OAuth2Request {

    public function __construct(array $values)
    {
        parent::__construct($values);
    }

    public function isValid()
    {
        $grant_type = $this->getGrantType();

        if(is_null($grant_type))
            return false;

        if(!array_key_exists($grant_type,OAuth2Protocol::$valid_grant_types))
            return false;

        $redirect_uri = $this->getRedirectUri();
        if(is_null($redirect_uri))
            return false;

        return true;
    }

    public function getClientId(){
        return isset($this[OAuth2Protocol::OAuth2Protocol_ClientId])?$this[OAuth2Protocol::OAuth2Protocol_ClientId]:null;
    }

    public function getRedirectUri(){
        return isset($this[OAuth2Protocol::OAuth2Protocol_RedirectUri])?$this[OAuth2Protocol::OAuth2Protocol_RedirectUri]:null;
    }

    public function getGrantType(){
        return isset($this[OAuth2Protocol::OAuth2Protocol_GrantType])?$this[OAuth2Protocol::OAuth2Protocol_GrantType]:null;
    }

    public function getCode(){
        return isset($this[OAuth2Protocol::OAuth2Protocol_ResponseType_Code])?$this[OAuth2Protocol::OAuth2Protocol_ResponseType_Code]:null;
    }
}