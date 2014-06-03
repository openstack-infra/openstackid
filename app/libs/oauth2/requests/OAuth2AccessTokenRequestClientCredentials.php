<?php

namespace oauth2\requests;

use oauth2\OAuth2Protocol;
use oauth2\OAuth2Message;

/**
 * Class OAuth2AccessTokenRequestClientCredentials
 * http://tools.ietf.org/html/rfc6749#section-4.4.2
 * @package oauth2\requests
 */
class OAuth2AccessTokenRequestClientCredentials  extends OAuth2TokenRequest {

    public function __construct(OAuth2Message $msg)
    {
        parent::__construct($msg);
    }

    public function isValid()
    {
        if (!parent::isValid())
            return false;

        return true;
    }

    public function getScope()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_Scope);
    }
} 