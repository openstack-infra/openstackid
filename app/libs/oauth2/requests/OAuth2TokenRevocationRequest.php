<?php

namespace oauth2\requests;

use oauth2\OAuth2Message;
use oauth2\OAuth2Protocol;

class OAuth2TokenRevocationRequest extends OAuth2Request {

    public function __construct(OAuth2Message $msg)
    {
        parent::__construct($msg);
    }

    public function isValid()
    {
        $this->last_validation_error = '';

        $token = $this->getToken();

        if(is_null($token)) {
            $this->last_validation_error = 'token not set';
            return false;
        }

        return true;
    }

    public function getToken(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_Token);
    }

    public function getTokenHint(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_TokenType_Hint);
    }
} 