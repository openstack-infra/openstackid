<?php

namespace openid\requests;

use openid\helpers\OpenIdUriHelper;
use openid\OpenIdMessage;
use openid\OpenIdProtocol;

class OpenIdCheckAuthenticationRequest extends OpenIdAuthenticationRequest
{

	private $op_endpoint_url;

	/**
	 * @param OpenIdMessage $message
	 * @param               $op_endpoint_url
	 */
	public function __construct(OpenIdMessage $message, $op_endpoint_url)
    {
        parent::__construct($message);
	    $this->op_endpoint_url = $op_endpoint_url;
    }

    public static function IsOpenIdCheckAuthenticationRequest(OpenIdMessage $message)
    {
        $mode = $message->getMode();
        if ($mode == OpenIdProtocol::CheckAuthenticationMode) return true;
        return false;
    }

    public function isValid()
    {
        $mode                = $this->getMode();
        $claimed_assoc       = $this->getAssocHandle();
        $claimed_nonce       = $this->getNonce();
        $claimed_sig         = $this->getSig();
        $claimed_op_endpoint = $this->getOPEndpoint();
        $claimed_identity    = $this->getClaimedId();
        $claimed_realm       = $this->getRealm();
        $claimed_returnTo    = $this->getReturnTo();
        $signed              = $this->getSigned();

	    $valid_realm = OpenIdUriHelper::checkRealm($claimed_realm, $claimed_returnTo);

	    $res = !is_null($mode) && !empty($mode) && $mode == OpenIdProtocol::CheckAuthenticationMode
		    && !is_null($claimed_returnTo) && !empty($claimed_returnTo) && OpenIdUriHelper::checkReturnTo($claimed_returnTo)
		    && !is_null($claimed_realm) && !empty($claimed_realm) && $valid_realm
		    && !is_null($claimed_assoc) && !empty($claimed_assoc)
		    && !is_null($claimed_sig) && !empty($claimed_sig)
		    && !is_null($signed) && !empty($signed)
		    && !is_null($claimed_nonce) && !empty($claimed_nonce)
		    && !is_null($claimed_op_endpoint) && !empty($claimed_op_endpoint) &&  $claimed_op_endpoint == $this->op_endpoint_url
		    && !is_null($claimed_identity) && !empty($claimed_identity) && OpenIdUriHelper::isValidUrl($claimed_identity);
        if (!$res) {
	        $msg = sprintf("return_to is empty? %b.",empty($claimed_returnTo)).PHP_EOL;
	        $msg = $msg.sprintf("realm is empty? %b.",empty($claimed_realm)).PHP_EOL;
	        $msg = $msg.sprintf("claimed_id is empty? %b.",empty($claimed_id)).PHP_EOL;
	        $msg = $msg.sprintf("identity is empty? %b.",empty($claimed_identity)).PHP_EOL;
	        $msg = $msg.sprintf("mode is empty? %b.",empty($mode)).PHP_EOL;
	        $msg = $msg.sprintf("is valid realm? %b.",$valid_realm).PHP_EOL;
	        throw new InvalidOpenIdMessageException($msg);
        }
        return $res;
    }

    public function getNonce()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_Nonce);
    }

    public function getSig()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_Sig);
    }

    public function getOPEndpoint()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_OpEndpoint);
    }

    public function getSigned()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_Signed);
    }

    public function getInvalidateHandle()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_InvalidateHandle);
    }
}
