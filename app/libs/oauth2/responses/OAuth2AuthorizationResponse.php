<?php

namespace oauth2\responses;
use oauth2\OAuth2Protocol;

class OAuth2AuthorizationResponse extends OAuth2IndirectResponse {

    public function setAuthorizationCode($code){
        $this[OAuth2Protocol::OAuth2Protocol_ResponseType_Code] = $code;
    }

    public function setState($state){
        $this[OAuth2Protocol::OAuth2Protocol_State] = $state;
    }

}