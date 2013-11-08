<?php

namespace openid\requests;

use openid\helpers\OpenIdUriHelper;
use openid\OpenIdMessage;
use openid\OpenIdProtocol;
use openid\services\Registry;
use openid\services\ServiceCatalog;

class OpenIdAuthenticationRequest extends OpenIdRequest
{

    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
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
        $return_to = $this->getReturnTo();
        $claimed_id = $this->getClaimedId();
        $identity = $this->getIdentity();
        $mode = $this->getMode();
        $realm = $this->getRealm();
        $valid_realm = OpenIdUriHelper::checkRealm($realm, $return_to);
        $valid_id = $this->isValidIdentifier($claimed_id, $identity);

        return !empty($return_to)
        && !empty($realm)
        && $valid_realm
        && !empty($claimed_id)
        && !empty($identity)
        && $valid_id
        && !empty($mode) && ($mode == OpenIdProtocol::ImmediateMode || $mode == OpenIdProtocol::SetupMode);
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
     * @param $claimed_id The Claimed Identifier.
     * @param $identity The OP-Local Identifier.
     * @return bool
     */
    private function isValidIdentifier($claimed_id, $identity)
    {
        /*
         * openid.claimed_id" and "openid.identity" SHALL be either both present or both absent.
         * If neither value is present, the assertion is not about an identifier, and will contain
         * other information in its payload, using extensions.
         */

        $server_configuration_service = Registry::getInstance()->get(ServiceCatalog::ServerConfigurationService);
        if (is_null($claimed_id) && is_null($identity))
            return false;
        //http://specs.openid.net/auth/2.0/identifier_select
        if ($claimed_id == $identity && $identity == OpenIdProtocol::IdentifierSelectType)
            return true;

        if (OpenIdUriHelper::isValidUrl($claimed_id) && OpenIdUriHelper::isValidUrl($identity)) {
            $identity_url_pattern = $server_configuration_service->getUserIdentityEndpointURL("@identifier");
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
