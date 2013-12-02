<?php

namespace openid\requests;

use openid\helpers\OpenIdUriHelper;
use openid\OpenIdMessage;
use openid\OpenIdProtocol;
use openid\services\OpenIdRegistry;

class OpenIdCheckAuthenticationRequest extends OpenIdAuthenticationRequest
{

    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
    }

    public static function IsOpenIdCheckAuthenticationRequest(OpenIdMessage $message)
    {
        $mode = $message->getMode();
        if ($mode == OpenIdProtocol::CheckAuthenticationMode) return true;
        return false;
    }

    public function isValid()
    {
        $mode = $this->getMode();
        $claimed_assoc = $this->getAssocHandle();
        $claimed_nonce = $this->getNonce();
        $claimed_sig = $this->getSig();
        $claimed_op_endpoint = $this->getOPEndpoint();
        $claimed_identity = $this->getClaimedId();
        $claimed_realm = $this->getRealm();
        $claimed_returnTo = $this->getReturnTo();
        $server_configuration_service = OpenIdRegistry::getInstance()->get("openid\\services\\IServerConfigurationService");
        if (
            !is_null($mode) && !empty($mode) && $mode == OpenIdProtocol::CheckAuthenticationMode
            && !is_null($claimed_returnTo) && !empty($claimed_returnTo) && OpenIdUriHelper::checkReturnTo($claimed_returnTo)
            && !is_null($claimed_realm) && !empty($claimed_realm) && OpenIdUriHelper::checkRealm($claimed_realm, $claimed_returnTo)
            && !is_null($claimed_assoc) && !empty($claimed_assoc)
            && !is_null($claimed_sig) && !empty($claimed_sig)
            && !is_null($claimed_nonce) && !empty($claimed_nonce)
            && !is_null($claimed_op_endpoint) && !empty($claimed_op_endpoint) && $server_configuration_service->getOPEndpointURL() == $claimed_op_endpoint
            && !is_null($claimed_identity) && !empty($claimed_identity) && OpenIdUriHelper::isValidUrl($claimed_identity)
        ) {
            return true;
        }
        return false;
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
