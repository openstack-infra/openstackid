<?php

namespace openid\extensions\implementations;

use openid\exceptions\InvalidOpenIdMessageException;
use openid\helpers\OpenIdErrorMessages;
use openid\OpenIdMessage;
use openid\requests\OpenIdRequest;
use oauth2\OAuth2Protocol;

class OpenIdOAuth2Request  extends OpenIdRequest {

    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
    }

    public function isValid()
    {
        //check identifier
        if (isset($this->message[OpenIdOAuth2Extension::paramNamespace('_')])
            && $this->message[OpenIdOAuth2Extension::paramNamespace('_')] == OpenIdOAuth2Extension::NamespaceUrl
        ) {
            if(is_null($this->getClientId()))
                throw new InvalidOpenIdMessageException(sprintf(OpenIdErrorMessages::OAuth2MissingRequiredParam,'client_id'));
            if(is_null($this->getScope()))
                throw new InvalidOpenIdMessageException(sprintf(OpenIdErrorMessages::OAuth2MissingRequiredParam,'scope'));
            if(is_null($this->getState()))
                throw new InvalidOpenIdMessageException(sprintf(OpenIdErrorMessages::OAuth2MissingRequiredParam,'state'));

            return true;
        }
        return false;
    }

    public function getClientId(){
        return isset($this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_ClientId, '_')])?
            $this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_ClientId, '_')]:null;
    }

    public function getScope(){
        return isset($this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_Scope, '_')])?
            $this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_Scope, '_')]:null;
    }

    public function getState(){
        return isset($this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_State, '_')])?
            $this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_State, '_')]:null;
    }
}