<?php

namespace oauth2\requests;

use oauth2\OAuth2Protocol;
use oauth2\OAuth2Message;
/**
 * Class OAuth2AccessTokenValidationRequest
 * @package oauth2\requests
 */

class OAuth2AccessTokenValidationRequest  extends OAuth2Request {

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
}