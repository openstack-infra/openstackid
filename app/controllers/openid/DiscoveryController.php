<?php

use openid\IOpenIdProtocol;
use openid\services\IServerConfigurationService;
use openid\XRDS\XRDSDocumentBuilder;
use utils\services\IAuthService;

class DiscoveryController extends OpenIdController
{

    private $openid_protocol;
    private $auth_service;
    private $server_config_service;

    public function __construct(IOpenIdProtocol $openid_protocol, IAuthService $auth_service, IServerConfigurationService $server_config_service)
    {
        $this->openid_protocol      = $openid_protocol;
        $this->auth_service         = $auth_service;
        $this->server_config_service = $server_config_service;
    }

    /**
     * XRDS discovery(eXtensible Resource Descriptor Sequence)
     * @return xrds document on response
     */
    public function idp()
    {
        $response = Response::make($this->openid_protocol->getXRDSDiscovery(IOpenIdProtocol::OpenIdXRDSModeIdp), 200);
        $this->setDiscoveryResponseType($response);
        return $response;
    }

    /**
     * If the Claimed Identifier was not previously discovered by the Relying Party
     * (the "openid.identity" in the request was "http://specs.openid.net/auth/2.0/identifier_select"
     * or a different Identifier, or if the OP is sending an unsolicited positive assertion),
     * the Relying Party MUST perform discovery on the Claimed Identifier in
     * the response to make sure that the OP is authorized to make assertions about the Claimed Identifier.
     * @param $identifier
     * @return mixed
     */
    public function user($identifier)
    {
        $user = $this->auth_service->getUserByOpenId($identifier);
        if (is_null($user))
            return View::make("404");

        $local_identifier = $this->server_config_service->getUserIdentityEndpointURL($identifier);
        $response = Response::make($this->openid_protocol->getXRDSDiscovery(IOpenIdProtocol::OpenIdXRDSModeUser, $local_identifier), 200);
        $this->setDiscoveryResponseType($response);
        return $response;
    }

}