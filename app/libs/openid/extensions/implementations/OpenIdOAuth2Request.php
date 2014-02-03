<?php

namespace openid\extensions\implementations;

use openid\exceptions\InvalidOpenIdMessageException;
use openid\helpers\OpenIdErrorMessages;
use openid\OpenIdMessage;
use openid\requests\OpenIdRequest;
use oauth2\OAuth2Protocol;

/**
 * Class OpenIdOAuth2Request
 * @package openid\extensions\implementations
 */
class OpenIdOAuth2Request  extends OpenIdRequest {

    /**
     * @param OpenIdMessage $message
     */
    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
    }

    /**
     * @return bool
     * @throws \openid\exceptions\InvalidOpenIdMessageException
     */
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

    /**
     * Indicates whether the user should be re-prompted for consent. The default is auto,
     * so a given user should only see the consent page for a given set of scopes the first time
     * through the sequence. If the value is force, then the user sees a consent page even if they
     * previously gave consent to your application for a given set of scopes.
     * @return null|string
     */
    public function getApprovalPrompt(){
        return isset($this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_Approval_Prompt, '_')])?
            $this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_Approval_Prompt, '_')]:OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto;
    }

    /**
     * Indicates whether your application needs to access an API when the user is not present at the browser.
     * This parameter defaults to online. If your application needs to refresh access tokens when the user is
     * not present at the browser, then use offline. This will result in your application obtaining a refresh
     * token the first time your application exchanges an authorization code for a user.
     * @return null|string
     */
    public function getAccessType(){
        return isset($this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_AccessType, '_')])?
            $this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_AccessType, '_')]:OAuth2Protocol::OAuth2Protocol_AccessType_Online;
    }

    /**
     * @return null|string
     */
    public function getClientId(){
        return isset($this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_ClientId, '_')])?
            $this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_ClientId, '_')]:null;
    }

    /**
     * @return null|string
     */
    public function getScope(){
        return isset($this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_Scope, '_')])?
            $this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_Scope, '_')]:null;
    }

    /**
     * @return null|string
     */
    public function getState(){
        return isset($this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_State, '_')])?
            $this->message[OpenIdOAuth2Extension::param(OAuth2Protocol::OAuth2Protocol_State, '_')]:null;
    }
}