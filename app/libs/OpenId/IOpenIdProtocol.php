<?php namespace OpenId;

/**
 * Interface IOpenIdProtocol
 * @package OpenId
 */
interface IOpenIdProtocol
{

    const OpenIdXRDSModeUser = "OpenIdXRDSModeUser";
    const OpenIdXRDSModeIdp = "OpenIdXRDSModeIdp";

    /**
     * With OpenID 2.0, the relying party discovers the OpenID provider URL by requesting
     * the XRDS document (also called the Yadis document) with the content type application/xrds+xml;
     * this document may be available at the target URL and is always available for a target XRI.
     * @param $mode
     * @param null $canonical_id
     * @return mixed
     */
    public function getXRDSDiscovery($mode, $canonical_id = null);

    /**
     * @param OpenIdMessage $openIdMessage
     * @return responses\OpenIdResponse response
     */
    public function handleOpenIdMessage(OpenIdMessage $openIdMessage);
}