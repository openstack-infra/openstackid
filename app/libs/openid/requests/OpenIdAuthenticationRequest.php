<?php

namespace openid\requests;

use openid\exceptions\InvalidOpenIdMessageException;
use openid\helpers\OpenIdUriHelper;
use openid\OpenIdMessage;
use openid\OpenIdProtocol;
/**
 * Class OpenIdAuthenticationRequest
 * @package openid\requests
 */
class OpenIdAuthenticationRequest extends OpenIdRequest
{

	private $user_identity_endpoint;

	/**
	 * @param OpenIdMessage $message
	 * @param null          $user_identity_endpoint
	 */
	public function __construct(OpenIdMessage $message, $user_identity_endpoint = null)
    {
        parent::__construct($message);
	    $this->user_identity_endpoint = $user_identity_endpoint;
	    if(!empty($this->user_identity_endpoint)){
		    if(!str_contains($this->user_identity_endpoint,'@identifier')){
			    throw new InvalidOpenIdMessageException("user_identity_endpoint value  must contain @identifier placeholder!.");
		    }
	    }
    }

    public static function IsOpenIdAuthenticationRequest(OpenIdMessage $message)
    {
        $mode = $message->getMode();
        if ($mode == OpenIdProtocol::ImmediateMode || $mode == OpenIdProtocol::SetupMode) return true;
        return false;
    }

    public function getAssocHandle()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_AssocHandle);
    }

    public function isValid()
    {

        $return_to   = $this->getReturnTo();
        $claimed_id  = $this->getClaimedId();
        $identity    = $this->getIdentity();
        $mode        = $this->getMode();
        $realm       = $this->getRealm();
	    $valid_id    = $this->isValidIdentifier($claimed_id, $identity);
	    $valid_realm = OpenIdUriHelper::checkRealm($realm, $return_to);

	    $res = !empty($return_to)
            && !empty($realm)
            && $valid_realm
            && !empty($claimed_id)
            && !empty($identity)
            && $valid_id
            && !empty($mode) && ($mode == OpenIdProtocol::ImmediateMode || $mode == OpenIdProtocol::SetupMode);

        if(!$res){
                $msg = sprintf("return_to is empty? %b.",empty($return_to)).PHP_EOL;
                $msg = $msg.sprintf("realm is empty? %b.",empty($realm)).PHP_EOL;
                $msg = $msg.sprintf("claimed_id is empty? %b.",empty($claimed_id)).PHP_EOL;
                $msg = $msg.sprintf("identity is empty? %b.",empty($identity)).PHP_EOL;
                $msg = $msg.sprintf("mode is empty? %b.",empty($mode)).PHP_EOL;
                $msg = $msg.sprintf("is valid realm? %b.",$valid_realm).PHP_EOL;
                $msg = $msg.sprintf("is valid identifier? %b.",$valid_id).PHP_EOL;
                throw new InvalidOpenIdMessageException($msg);
        }

        return $res;
    }

    public function getReturnTo()
    {
        $return_to = $this->getParam(OpenIdProtocol::OpenIDProtocol_ReturnTo);
        return (OpenIdUriHelper::checkReturnTo($return_to)) ? $return_to : "";
    }

    public function getClaimedId()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_ClaimedId);
    }

    public function getIdentity()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_Identity);
    }

    public function getRealm()
    {
        $realm = $this->getParam(OpenIdProtocol::OpenIDProtocol_Realm);
        return $realm;
    }

    public function isIdentitySelectByOP(){
        $claimed_id = $this->getClaimedId();
        $identity   = $this->getIdentity();
        //http://specs.openid.net/auth/2.0/identifier_select
        if ($claimed_id == $identity && $identity == OpenIdProtocol::IdentifierSelectType)
            return true;
        return false;
    }

	/**
	 * @param $claimed_id
	 * @param $identity
	 * @return bool
	 * @throws \openid\exceptions\InvalidOpenIdMessageException
	 */
	private function isValidIdentifier($claimed_id, $identity)
    {
        /*
         * openid.claimed_id" and "openid.identity" SHALL be either both present or both absent.
         * If neither value is present, the assertion is not about an identifier, and will contain
         * other information in its payload, using extensions.
         */

		if(empty($this->user_identity_endpoint))
			throw new InvalidOpenIdMessageException("user_identity_endpoint is not set");

        if (is_null($claimed_id) && is_null($identity))
            return false;
        //http://specs.openid.net/auth/2.0/identifier_select
        if ($claimed_id == $identity && $identity == OpenIdProtocol::IdentifierSelectType)
            return true;

        if (OpenIdUriHelper::isValidUrl($claimed_id) && OpenIdUriHelper::isValidUrl($identity)) {
            $identity_url_pattern = $this->user_identity_endpoint;
            $url_parts = explode("@", $identity_url_pattern, 2);
            $base_identity_url = $url_parts[0];
            if (strpos($identity, $base_identity_url) !== false)
                return true;
            if (strpos($claimed_id, $base_identity_url) !== false)
                return true;
        }
        return false;
    }

}
