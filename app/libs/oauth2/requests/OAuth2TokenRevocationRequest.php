<?php

namespace oauth2\requests;

use oauth2\OAuth2Message;
use oauth2\OAuth2Protocol;

class OAuth2TokenRevocationRequest extends OAuth2TokenRequest {

    public function __construct(OAuth2Message $msg)
    {
        parent::__construct($msg);
    }

    public function isValid()
    {
        $token = $this->getToken();

        if(is_null($token))
            return false;

        return true;
    }

    public function getToken(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_Token);
    }

    public function getTokenHint(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_TokenType_Hint);
    }
} 