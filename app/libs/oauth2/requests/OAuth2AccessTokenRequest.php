<?php

namespace oauth2\requests;

use oauth2\OAuth2Protocol;

/**
 * Class OAuth2AccessTokenRequest
 * http://tools.ietf.org/html/rfc6749#section-4.1.3
 * @package oauth2\requests
 */
class OAuth2AccessTokenRequest extends OAuth2Request {

    private $msg;

    public function __construct(OAuth2TokenRequest $msg)
    {
        parent::__construct($msg->container);
        $this->msg = $msg;
    }

    public function isValid()
    {
        if(!$this->msg->isValid())
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

    public function getCode(){
        return isset($this[OAuth2Protocol::OAuth2Protocol_ResponseType_Code])?$this[OAuth2Protocol::OAuth2Protocol_ResponseType_Code]:null;
    }
}