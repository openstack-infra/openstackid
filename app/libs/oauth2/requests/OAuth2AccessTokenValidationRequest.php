<?php

namespace oauth2\requests;

use oauth2\OAuth2Protocol;
use oauth2\grant_types\ValidateBearerTokenGrantType;

/**
 * Class OAuth2AccessTokenValidationRequest
 * @package oauth2\requests
 */

class OAuth2AccessTokenValidationRequest extends OAuth2Request{

    public function __construct(array $values)
    {
        parent::__construct($values);
    }

    public function isValid()
    {
        $grant_type = $this->getGrantType();

        if(is_null($grant_type))
            return false;

        if($grant_type!==ValidateBearerTokenGrantType::OAuth2Protocol_GrantType_Extension_ValidateBearerToken)
            return false;

        $token = $this->getToken();

        if(is_null($token))
            return false;

        return true;
    }

    public function getGrantType(){
        return isset($this[OAuth2Protocol::OAuth2Protocol_GrantType])?$this[OAuth2Protocol::OAuth2Protocol_GrantType]:null;
    }

    public function getToken(){
        return isset($this[OAuth2Protocol::OAuth2Protocol_Token])?$this[OAuth2Protocol::OAuth2Protocol_Token]:null;
    }
}