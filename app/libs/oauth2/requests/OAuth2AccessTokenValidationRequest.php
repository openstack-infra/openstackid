<?php

namespace oauth2\requests;

use oauth2\OAuth2Protocol;

/**
 * Class OAuth2AccessTokenValidationRequest
 * @package oauth2\requests
 */

class OAuth2AccessTokenValidationRequest  extends OAuth2Request{

    private $msg;

    public function __construct(OAuth2TokenRequest $msg)
    {
        parent::__construct($msg->container);
        $this->msg = $msg;
    }

    public function isValid()
    {
        if(!$this->msgisValid())
            return false;

        $token = $this->getToken();

        if(is_null($token))
            return false;

        return true;
    }

    public function getToken(){
        return isset($this[OAuth2Protocol::OAuth2Protocol_Token])?$this[OAuth2Protocol::OAuth2Protocol_Token]:null;
    }
}