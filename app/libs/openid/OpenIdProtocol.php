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

use openid\XRDS\XRDSService;
use openid\XRDS\XRDSDocumentBuilder;
use openid\IOpenIdProtocol;

class OpenIdProtocol implements IOpenIdProtocol {

    const OpenIdPrefix          = "openid";
    //protocol constants
    const OPIdentifierType        = "http://specs.openid.net/auth/2.0/server";
    const ClaimedIdentifierType   = "http://specs.openid.net/auth/2.0/signon";
    const OpenID2MessageType      = "http://specs.openid.net/auth/2.0";
    const IdentifierSelectType    = "http://specs.openid.net/auth/2.0/identifier_select";

    const ImmediateMode           = "checkid_immediate";
    const SetupMode               = "checkid_setup";
    const IdMode                  = "id_res";
    const SetupNeededMode         = "setup_needed";
    const CancelMode              = "cancel";
    const CheckAuthenticationMode = "check_authentication";
    const ErrorMode               = "error";
    const AssociateMode           = "associate";

    const SignatureAlgorithmHMAC_SHA1       = "HMAC-SHA1";
    const SignatureAlgorithmHMAC_SHA256     = "HMAC-SHA256";

    const OpenIDProtocol_Mode               = "mode";
    const OpenIDProtocol_NS                 = "ns";
    const OpenIDProtocol_ReturnTo           = "return_to";
    const OpenIDProtocol_ClaimedId          = "claimed_id";
    const OpenIDProtocol_Identity           = "identity";
    const OpenIDProtocol_AssocHandle        = "assoc_handle";
    const OpenIDProtocol_Realm              = "realm";
    const OpenIDProtocol_OpEndpoint         = "op_endpoint";
    const OpenIDProtocol_Nonce              = "response_nonce";
    const OpenIDProtocol_InvalidateHandle   = "invalidate_handle";
    const OpenIDProtocol_Signed             = "signed";
    const OpenIDProtocol_Sig                = "sig";
    const OpenIDProtocol_Error              = "error";
    const OpenIDProtocol_Contact            = "contact";
    const OpenIDProtocol_Reference          = "reference";



    private static $OpenIDProtocol_ValidModes = array(
        self::ImmediateMode,
        self::SetupMode,
        self::IdMode,
        self::SetupNeededMode,
        self::CancelMode,
        self::CheckAuthenticationMode,
        self::ErrorMode,
        self::AssociateMode,
    );

    private static $protocol_definition = array(
        self::OpenIDProtocol_Mode             => self::OpenIDProtocol_Mode,
        self::OpenIDProtocol_NS               => self::OpenIDProtocol_NS,
        self::OpenIDProtocol_ReturnTo         => self::OpenIDProtocol_ReturnTo,
        self::OpenIDProtocol_ClaimedId        => self::OpenIDProtocol_ClaimedId,
        self::OpenIDProtocol_Identity         => self::OpenIDProtocol_Identity,
        self::OpenIDProtocol_AssocHandle      => self::OpenIDProtocol_AssocHandle,
        self::OpenIDProtocol_Realm            => self::OpenIDProtocol_Realm,
        self::OpenIDProtocol_OpEndpoint       => self::OpenIDProtocol_OpEndpoint,
        self::OpenIDProtocol_Nonce            => self::OpenIDProtocol_Nonce,
        self::OpenIDProtocol_InvalidateHandle => self::OpenIDProtocol_InvalidateHandle,
        self::OpenIDProtocol_Signed           => self::OpenIDProtocol_Signed,
        self::OpenIDProtocol_Sig              => self::OpenIDProtocol_Sig,
        self::OpenIDProtocol_Error            => self::OpenIDProtocol_Error,
        self::OpenIDProtocol_Contact          => self::OpenIDProtocol_Contact,
        self::OpenIDProtocol_Reference        => self::OpenIDProtocol_Reference,
    );

    /**
     * check if a provide message mode is valid or not in openid 2.0 protocol
     * @param $mode
     * @return bool
     */
    public static function isValidMode($mode){
            return in_array($mode,self::$OpenIDProtocol_ValidModes);
    }

    public static function param($param, $separator='.'){
        return self::OpenIdPrefix.$separator.self::$protocol_definition[$param];
    }

    private $request_handlers;

    public function __construct(){
        //create chain of responsibility
        $auth_service                   = \App::make("openid\\services\\IAuthService");
        $memento_request_service        = \App::make("openid\\services\\IMementoOpenIdRequestService");
        $auth_strategy                  = \App::make("openid\\handlers\\IOpenIdAuthenticationStrategy");
        $server_extension_service       = \App::make("openid\\services\\IServerExtensionsService");
        $association_service            = \App::make("openid\\services\\IAssociationService");
        $trusted_sites_service          = \App::make("openid\\services\\ITrustedSitesService");
        $server_config_service          = \App::make("openid\\services\\IServerConfigurationService");

        $successor                      = new OpenIdSessionAssociationRequestHandler(new OpenIdCheckAuthenticationRequestHandler(null));
        $this->request_handlers         = new OpenIdAuthenticationRequestHandler($auth_service,$memento_request_service,$auth_strategy,$server_extension_service,$association_service,$trusted_sites_service,$server_config_service,$successor);
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