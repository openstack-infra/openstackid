<?php

namespace openid;

use openid\handlers\OpenIdAuthenticationRequestHandler;
use openid\handlers\OpenIdCheckAuthenticationRequestHandler;
use openid\handlers\OpenIdSessionAssociationRequestHandler;
use openid\services\OpenIdServiceCatalog;
use openid\XRDS\XRDSDocumentBuilder;
use openid\XRDS\XRDSService;
use utils\services\Registry;
use utils\services\UtilsServiceCatalog;

/**
 * Class OpenIdProtocol
 * OpenId Protocol Implementation
 * @package openid
 */
class OpenIdProtocol implements IOpenIdProtocol
{

    const OpenIdPrefix = "openid";
    //protocol constants
    const OPIdentifierType = "http://specs.openid.net/auth/2.0/server";
    const ClaimedIdentifierType = "http://specs.openid.net/auth/2.0/signon";
    const OpenID2MessageType = "http://specs.openid.net/auth/2.0";
    const IdentifierSelectType = "http://specs.openid.net/auth/2.0/identifier_select";
    const ImmediateMode = "checkid_immediate";
    const SetupMode = "checkid_setup";
    const IdMode = "id_res";
    const SetupNeededMode = "setup_needed";
    const CancelMode = "cancel";
    const CheckAuthenticationMode = "check_authentication";
    const ErrorMode = "error";
    const AssociateMode = "associate";
    const SignatureAlgorithmHMAC_SHA1 = "HMAC-SHA1";
    const SignatureAlgorithmHMAC_SHA256 = "HMAC-SHA256";
    const AssociationSessionTypeNoEncryption = "no-encryption";
    const AssociationSessionTypeDHSHA1 = "DH-SHA1";
    const AssociationSessionTypeDHSHA256 = "DH-SHA256";
    const OpenIDProtocol_Mode = "mode";
    const OpenIDProtocol_NS = "ns";
    const OpenIDProtocol_ReturnTo = "return_to";
    const OpenIDProtocol_ClaimedId = "claimed_id";
    const OpenIDProtocol_Identity = "identity";
    const OpenIDProtocol_AssocHandle = "assoc_handle";
    const OpenIDProtocol_Realm = "realm";
    const OpenIDProtocol_OpEndpoint = "op_endpoint";
    const OpenIDProtocol_Nonce = "response_nonce";
    const OpenIDProtocol_InvalidateHandle = "invalidate_handle";
    const OpenIDProtocol_Signed = "signed";
    const OpenIDProtocol_Sig = "sig";
    const OpenIDProtocol_Error = "error";
    const OpenIDProtocol_ErrorCode = "error_code";
    const OpenIDProtocol_Contact = "contact";
    const OpenIDProtocol_Reference = "reference";
    const OpenIDProtocol_IsValid = "is_valid";
    const OpenIDProtocol_AssocType = "assoc_type";
    const OpenIDProtocol_SessionType = "session_type";
    const OpenIdProtocol_DHModulus = "dh_modulus";
    const OpenIdProtocol_DHGen = "dh_gen";
    const OpenIdProtocol_DHConsumerPublic = "dh_consumer_public";
    const OpenIdProtocol_ExpiresIn = "expires_in";
    const OpenIdProtocol_DHServerPublic = "dh_server_public";
    const OpenIdProtocol_DHEncMacKey = "enc_mac_key ";
    const OpenIdProtocol_MacKey = "mac_key ";
    private static $OpenIDProtocol_SupportedAssocType = array(
        self::SignatureAlgorithmHMAC_SHA1,
        self::SignatureAlgorithmHMAC_SHA256
    );
    private static $OpenIDProtocol_SupportedSessionType = array(
        self::AssociationSessionTypeNoEncryption,
        self::AssociationSessionTypeDHSHA1,
        self::AssociationSessionTypeDHSHA256
    );
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
        self::OpenIDProtocol_Mode => self::OpenIDProtocol_Mode,
        self::OpenIDProtocol_NS => self::OpenIDProtocol_NS,
        self::OpenIDProtocol_ReturnTo => self::OpenIDProtocol_ReturnTo,
        self::OpenIDProtocol_ClaimedId => self::OpenIDProtocol_ClaimedId,
        self::OpenIDProtocol_Identity => self::OpenIDProtocol_Identity,
        self::OpenIDProtocol_AssocHandle => self::OpenIDProtocol_AssocHandle,
        self::OpenIDProtocol_Realm => self::OpenIDProtocol_Realm,
        self::OpenIDProtocol_OpEndpoint => self::OpenIDProtocol_OpEndpoint,
        self::OpenIDProtocol_Nonce => self::OpenIDProtocol_Nonce,
        self::OpenIDProtocol_InvalidateHandle => self::OpenIDProtocol_InvalidateHandle,
        self::OpenIDProtocol_Signed => self::OpenIDProtocol_Signed,
        self::OpenIDProtocol_Sig => self::OpenIDProtocol_Sig,
        self::OpenIDProtocol_Error => self::OpenIDProtocol_Error,
        self::OpenIDProtocol_ErrorCode => self::OpenIDProtocol_ErrorCode,
        self::OpenIDProtocol_Contact => self::OpenIDProtocol_Contact,
        self::OpenIDProtocol_Reference => self::OpenIDProtocol_Reference,
        self::OpenIDProtocol_IsValid => self::OpenIDProtocol_IsValid,
        self::OpenIDProtocol_AssocType => self::OpenIDProtocol_AssocType,
        self::OpenIDProtocol_SessionType => self::OpenIDProtocol_SessionType,
        self::OpenIdProtocol_DHModulus => self::OpenIdProtocol_DHModulus,
        self::OpenIdProtocol_DHGen => self::OpenIdProtocol_DHGen,
        self::OpenIdProtocol_DHConsumerPublic => self::OpenIdProtocol_DHConsumerPublic,
        self::OpenIdProtocol_ExpiresIn => self::OpenIdProtocol_ExpiresIn,
        self::OpenIdProtocol_DHServerPublic => self::OpenIdProtocol_DHServerPublic,
        self::OpenIdProtocol_DHEncMacKey => self::OpenIdProtocol_DHEncMacKey,
        self::OpenIdProtocol_MacKey => self::OpenIdProtocol_MacKey,
    );
    private $request_handlers;

    public function __construct()
    {
        //create chain of responsibility

        $auth_service = Registry::getInstance()->get(UtilsServiceCatalog::AuthenticationService);
        $memento_request_service = Registry::getInstance()->get(OpenIdServiceCatalog::MementoService);
        $auth_strategy = Registry::getInstance()->get(OpenIdServiceCatalog::AuthenticationStrategy);
        $server_extension_service = Registry::getInstance()->get(OpenIdServiceCatalog::ServerExtensionsService);
        $association_service = Registry::getInstance()->get(OpenIdServiceCatalog::AssociationService);
        $trusted_sites_service = Registry::getInstance()->get(OpenIdServiceCatalog::TrustedSitesService);
        $server_config_service = Registry::getInstance()->get(OpenIdServiceCatalog::ServerConfigurationService);
        $nonce_service = Registry::getInstance()->get(OpenIdServiceCatalog::NonceService);
        $log = Registry::getInstance()->get(UtilsServiceCatalog::LogService);

        $check_auth = new OpenIdCheckAuthenticationRequestHandler($association_service, $nonce_service, $log, null);
        $session_assoc = new OpenIdSessionAssociationRequestHandler($log, $check_auth);
        $this->request_handlers = new OpenIdAuthenticationRequestHandler($auth_service, $memento_request_service, $auth_strategy, $server_extension_service, $association_service, $trusted_sites_service, $server_config_service, $nonce_service, $log, $session_assoc);
    }

    public static function isAssocTypeSupported($assoc_type)
    {
        return in_array($assoc_type, self::$OpenIDProtocol_SupportedAssocType);
    }

    public static function isSessionTypeSupported($session_type)
    {
        return in_array($session_type, self::$OpenIDProtocol_SupportedSessionType);
    }

    /**
     * check if a provide message mode is valid or not in openid 2.0 protocol
     * @param $mode
     * @return bool
     */
    public static function isValidMode($mode)
    {
        return in_array($mode, self::$OpenIDProtocol_ValidModes);
    }

    public static function param($param, $separator = '.')
    {
        return self::OpenIdPrefix . $separator . self::$protocol_definition[$param];
    }

    public function getXRDSDiscovery($mode, $canonical_id = null)
    {
        $server_extension_service = Registry::getInstance()->get(OpenIdServiceCatalog::ServerExtensionsService);
        $server_config_service = Registry::getInstance()->get(OpenIdServiceCatalog::ServerConfigurationService);

        $active_extensions = $server_extension_service->getAllActiveExtensions();
        $extensions = array();
        foreach ($active_extensions as $ext) {
            array_push($extensions, $ext->getNamespace());
        }

        $services = array();
        array_push($services, new XRDSService(0, $mode == IOpenIdProtocol::OpenIdXRDSModeUser ? self::ClaimedIdentifierType : self::OPIdentifierType, $server_config_service->getOPEndpointURL(), $extensions, $canonical_id));
        $builder = new XRDSDocumentBuilder($services, $canonical_id);
        $xrds = $builder->render();
        return $xrds;
    }

    public function handleOpenIdMessage(OpenIdMessage $openIdMessage)
    {
        return $this->request_handlers->handleMessage($openIdMessage);
    }
}