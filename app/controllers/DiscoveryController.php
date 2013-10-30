<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 12:29 PM
 * To change this template use File | Settings | File Templates.
 */

use openid\IOpenIdProtocol;
use openid\XRDS\XRDSDocumentBuilder;
use \openid\services\IAuthService;
use openid\services\IServerConfigurationService;

class DiscoveryController extends BaseController {

    private $openid_protocol;
    private $auth_service;
    private $server_config_service;

    public function __construct(IOpenIdProtocol $openid_protocol,IAuthService $auth_service, IServerConfigurationService $server_config_service){
        $this->openid_protocol       = $openid_protocol;
        $this->auth_service          = $auth_service;
        $this->server_config_service = $server_config_service;
    }

    /**
     * XRDS discovery(eXtensible Resource Descriptor Sequence)
     * @return xrds document on response
     */
    public function idp(){
        //This field contains a semicolon-separated list of representation schemes
        //which will be accepted in the response to this request.
        $accept = Request::header('Accept');
        $accept_values = explode(",",$accept);
        if(in_array(XRDSDocumentBuilder::ContentType,$accept_values))
        {
            $response = Response::make($this->openid_protocol->getXRDSDiscovery(IOpenIdProtocol::OpenIdXRDSModeIdp), 200);
            $response->header('Content-Type', "application/xrds+xml; charset=UTF-8");
        }
        else{
            $response = View::make("home");
        }
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
    public function user($identifier){
        $user = $this->auth_service->getUserByOpenId($identifier);
        if(is_null($user))
           return View::make("404");
        //This field contains a semicolon-separated list of representation schemes
        //which will be accepted in the response to this request.
        $accept = Request::header('Accept');
        $local_identifier = $this->server_config_service->getUserIdentityEndpointURL($identifier);
        $accept_values = explode(",",$accept);
        if(in_array(XRDSDocumentBuilder::ContentType,$accept_values))
        {
            $response = Response::make($this->openid_protocol->getXRDSDiscovery(IOpenIdProtocol::OpenIdXRDSModeUser,$local_identifier), 200);
            $response->header('Content-Type', "application/xrds+xml; charset=UTF-8");
        }
        else{
            $response = View::make("identity");
        }
        return $response;
    }

}