<?php

namespace oauth2\requests;

use oauth2\OAuth2Protocol;
use oauth2\OAuth2Message;

/**
 * Class OAuth2AccessTokenRequest
 * http://tools.ietf.org/html/rfc6749#section-4.1.3
 * @package oauth2\requests
 */
class OAuth2AccessTokenRequest extends OAuth2TokenRequest
{


    public function __construct(OAuth2Message $msg)
    {
        parent::__construct($msg);
    }

    public function isValid()
    {
        if (!parent::isValid())
            return false;

        $redirect_uri = $this->getRedirectUri();
        if (is_null($redirect_uri))
            return false;

        return true;
    }

    public function getRedirectUri()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_RedirectUri);
    }

    public function getClientId()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_ClientId);
    }

    public function getCode()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_ResponseType_Code);
    }
}