<?php
namespace oauth2\requests;


use oauth2\OAuth2Protocol;

/**
 * Class OAuth2TokenRequest
 * Base Token Request
 * @package oauth2\requests
 */
class OAuth2TokenRequest extends OAuth2Request {

    public function __construct(array $values)
    {
        parent::__construct($values);
    }

    public function isValid()
    {
        $grant_type = $this->getGrantType();

        if(is_null($grant_type))
            return false;

        return true;
    }

    public function getGrantType(){
        return isset($this[OAuth2Protocol::OAuth2Protocol_GrantType])?$this[OAuth2Protocol::OAuth2Protocol_GrantType]:null;
    }
} 