<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 4:08 PM
 * To change this template use File | Settings | File Templates.
 */



namespace openid;

use openid\handlers\OpenIdAuthenticationRequestHandler;
use openid\handlers\OpenIdSessionAssociationRequestHandler;
use openid\handlers\OpenIdCheckAuthenticationRequestHandler;

use openid\repositories\IServerExtensionsRepository;
use openid\repositories\IServerConfigurationRepository;
use openid\XRDS\XRDSService;
use openid\XRDS\XRDSDocumentBuilder;
use openid\IOpenIdProtocol;

class OpenIdProtocol implements IOpenIdProtocol {

    const  OPIdentifierType      = "http://specs.openid.net/auth/2.0/server";
    const  ClaimedIdentifierType = "http://specs.openid.net/auth/2.0/signon";

    private $server_extension_repository;
    private $server_configuration;
    private $request_handlers;

    public function __construct(IServerConfigurationRepository $server_configuration,IServerExtensionsRepository $server_extension_repository){
        $this->server_extension_repository = $server_extension_repository;
        $this->server_configuration        = $server_configuration;

        //create chain of responsibility
        $authService           = \App::make("openid\\services\\IAuthService");
        $mementoRequestService = \App::make("openid\\services\\IMementoOpenIdRequestService");
        $auth_strategy         = \App::make("openid\\handlers\\IOpenIdAuthenticationStrategy");

        $successor              = new OpenIdSessionAssociationRequestHandler(new OpenIdCheckAuthenticationRequestHandler(null));
        $this->request_handlers = new OpenIdAuthenticationRequestHandler($authService,$mementoRequestService,$auth_strategy,$successor);
    }

    public function getXRDSDiscovery(){

        $active_extensions = $this->server_extension_repository->GetAllExtensions();
        $extensions = array();
        foreach($active_extensions as $ext){
            array_push($extensions,$ext->namespace);
        }

        $services = array();
        array_push($services, new XRDSService(0,self::OPIdentifierType,$this->server_configuration->getOPEndpointURL(),$extensions));
        $builder = new XRDSDocumentBuilder($services);
        $xrds = $builder->render();
        return $xrds;
    }

    public function getHtmlDiscovery(){

    }

    public function HandleOpenIdMessage(OpenIdMessage $openIdMessage){
        return $this->request_handlers->HandleMessage($openIdMessage);
    }
}