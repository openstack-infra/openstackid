<?php

namespace oauth2\requests;

use oauth2\OAuth2Protocol;
use oauth2\OAuth2Message;

/**
 * Class OAuth2AuthorizationRequest
 * http://tools.ietf.org/html/rfc6749#section-4.1.1
 * @package oauth2\requests
 */
class OAuth2AuthorizationRequest extends OAuth2Request {

    public function __construct(OAuth2Message $msg)
    {
        parent::__construct($msg);
    }

    public static $params = array(
        OAuth2Protocol::OAuth2Protocol_ResponseType     => OAuth2Protocol::OAuth2Protocol_ResponseType,
        OAuth2Protocol::OAuth2Protocol_ClientId         => OAuth2Protocol::OAuth2Protocol_ClientId,
        OAuth2Protocol::OAuth2Protocol_RedirectUri      => OAuth2Protocol::OAuth2Protocol_RedirectUri,
        OAuth2Protocol::OAuth2Protocol_Scope            => OAuth2Protocol::OAuth2Protocol_Scope,
        OAuth2Protocol::OAuth2Protocol_State            => OAuth2Protocol::OAuth2Protocol_State,
	    OAuth2Protocol::OAuth2Protocol_Approval_Prompt  => OAuth2Protocol::OAuth2Protocol_Approval_Prompt,
	    OAuth2Protocol::OAuth2Protocol_AccessType       => OAuth2Protocol::OAuth2Protocol_AccessType,
    );

    /**
     * @return null|string
     */
    public function getResponseType(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_ResponseType);
    }

    /**
     * Identifies the client that is making the request.
     * The value passed in this parameter must exactly match the value shown in the Admin Console.
     * @return null|string
     */
    public function getClientId(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_ClientId);
    }

    /**
     * One of the redirect_uri values registered
     * @return null|string
     */
    public function getRedirectUri(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_RedirectUri);
    }

    /**
     * Space-delimited set of permissions that the application requests.
     * @return null|string
     */
    public function getScope(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_Scope);
    }

    /**
     * Provides any state that might be useful to your application upon receipt of the response.
     * The Authorization Server roundtrips this parameter, so your application receives the same value it sent.
     * Possible uses include redirecting the user to the correct resource in your site, nonces, and
     * cross-site-request-forgery mitigations.
     * @return null|string
     */
    public function getState(){
        return $this->getParam(OAuth2Protocol::OAuth2Protocol_State);
    }

    /**
     * Indicates whether the user should be re-prompted for consent. The default is auto,
     * so a given user should only see the consent page for a given set of scopes the first time
     * through the sequence. If the value is force, then the user sees a consent page even if they
     * previously gave consent to your application for a given set of scopes.
     * @return null|string
     */
    public function getApprovalPrompt(){
        $approval = $this->getParam(OAuth2Protocol::OAuth2Protocol_Approval_Prompt);
        if(is_null($approval))
            $approval = OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto;
        return $approval;
    }

    /**
     * Indicates whether your application needs to access an API when the user is not present at the browser.
     * This parameter defaults to online. If your application needs to refresh access tokens when the user is
     * not present at the browser, then use offline. This will result in your application obtaining a refresh
     * token the first time your application exchanges an authorization code for a user.
     * @return null|string
     */
    public function getAccessType(){
        $access_type = $this->getParam(OAuth2Protocol::OAuth2Protocol_AccessType);
        if(is_null($access_type))
            $access_type = OAuth2Protocol::OAuth2Protocol_AccessType_Online;
        return $access_type;
    }

    /**
     * Validates current request
     * @return bool
     */
    public function isValid()
    {
        if(is_null($this->getResponseType()))
            return false;

        if(is_null($this->getClientId()))
            return false;

        if(is_null($this->getRedirectUri()))
            return false;
        //approval_prompt
        $valid_approvals = array(OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto,OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Force);
        if(!in_array($this->getApprovalPrompt(),$valid_approvals)){
            return false;
        }
        return true;
    }
}
