<?php

namespace oauth2\requests;

use oauth2\OAuth2Protocol;
use oauth2\OAuth2Message;

class OAuth2RefreshAccessTokenRequest extends OAuth2TokenRequest {

    public function __construct(OAuth2Message $msg)
    {
        parent::__construct($msg);
    }

    public function isValid()
    {
        if(!parent::isValid())
            return false;

        $refresh_token = $this->getRefreshToken();

        if(is_null($refresh_token)) {
            $this->last_validation_error = 'refresh_token not set';
            return false;
        }

        return true;
    }

    public function getRefreshToken(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_RefreshToken);
    }

    public function getScope(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_Scope);
    }
} 