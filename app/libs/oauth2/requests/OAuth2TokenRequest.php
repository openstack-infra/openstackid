<?php

namespace oauth2\requests;

use oauth2\OAuth2Message;
use oauth2\OAuth2Protocol;

/**
 * Class OAuth2TokenRequest
 * Base Token Request
 * @package oauth2\requests
 */
class OAuth2TokenRequest extends OAuth2Request
{

    public function __construct(OAuth2Message $msg)
    {
        parent::__construct($msg);
    }

    public function isValid()
    {
        $this->last_validation_error = '';

        $grant_type = $this->getGrantType();

        if(is_null($grant_type)) {
            $this->last_validation_error = 'grant_type not set';
            return false;
        }

        return true;
    }

    public function getGrantType()
    {
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_GrantType);
    }
} 