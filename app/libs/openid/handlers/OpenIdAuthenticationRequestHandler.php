<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:43 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\handlers;

use openid\OpenIdMessage;
use openid\OpenIdProtocol;
use openid\requests\OpenIdAuthenticationRequest;
use openid\services\IAuthService;
use openid\services\IMementoOpenIdRequestService;
use openid\services\IServerExtensionsService;
use openid\services\IAssociationService;
use openid\requests\contexts\RequestContext;
use openid\responses\contexts\ResponseContext;
use openid\exceptions\InvalidOpenIdAuthenticationRequestMode;
use openid\responses\OpenIdNonImmediateNegativeAssertion;
use openid\responses\OpenIdImmediateNegativeAssertion;
use openid\services\ITrustedSitesService;
use openid\responses\OpenIdIndirectResponse;
use openid\responses\OpenIdIndirectGenericErrorResponse;
use openid\helpers\OpenIdErrorMessages;
use openid\helpers\OpenIdCryptoHelper;
use openid\model\IAssociation;
use openid\responses\OpenIdPositiveAssertionResponse;
use openid\services\IServerConfigurationService;
use openid\helpers\OpenIdSignatureBuilder;
use openid\exceptions\InvalidOpenIdMessageException;
use openid\model\ITrustedSite;
/**
 * Class OpenIdAuthenticationRequestHandler
 * Implements
 * http://openid.net/specs/openid-authentication-2_0.html#requesting_authentication
 * http://openid.net/specs/openid-authentication-2_0.html#responding_to_authentication
 * @package openid\handlers
 */
class OpenIdAuthenticationRequestHandler extends OpenIdMessageHandler
{
    private $authService;
    private $mementoRequestService;
    private $auth_strategy;
    private $server_extensions_service;
    private $association_service;
    private $trusted_sites_service;
    private $server_configuration_service;
    private $extensions;
    private $current_request;
    private $current_request_context;

    public function __construct(IAuthService $authService,
                                IMementoOpenIdRequestService $mementoRequestService,
                                IOpenIdAuthenticationStrategy $auth_strategy,
                                IServerExtensionsService $server_extensions_service,
                                IAssociationService $association_service,
                                ITrustedSitesService $trusted_sites_service,
                                IServerConfigurationService $server_configuration_service,
                                $successor)
    {
        parent::__construct($successor);
        $this->authService = $authService;
        $this->mementoRequestService = $mementoRequestService;
        $this->auth_strategy = $auth_strategy;
        $this->server_extensions_service = $server_extensions_service;
        $this->association_service = $association_service;
        $this->trusted_sites_service = $trusted_sites_service;
        $this->server_configuration_service = $server_configuration_service;
        $this->extensions = $this->server_extensions_service->getAllActiveExtensions();
    }


    /**
     * Create Positive Identity Assertion
     * implements http://openid.net/specs/openid-authentication-2_0.html#positive_assertions
     * @return OpenIdPositiveAssertionResponse
     */
    private function doAssertion()
    {

        $currentUser = $this->authService->getCurrentUser();
        $context = new ResponseContext;

        //initial signature params
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint));
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo));
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Nonce));
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocHandle));
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId));
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity));

        $op_endpoint = $this->server_configuration_service->getOPEndpointURL();
        $identity = $this->server_configuration_service->getUserIdentityEndpointURL($currentUser->getIdentifier());
        $response = new OpenIdPositiveAssertionResponse($op_endpoint, $identity, $identity, $this->current_request->getReturnTo());
        foreach ($this->extensions as $ext) {
            $ext->prepareResponse($this->current_request, $response, $context);
        }
        //check former assoc handle...
        $assoc_handle = $this->current_request->getAssocHandle();
        $association = $this->association_service->getAssociation($assoc_handle);
        if (empty($assoc_handle) || is_null($association)) {
            // if not present or if it already void then enter on dumb mode
            $new_secret = OpenIdCryptoHelper::generateSecret(OpenIdProtocol::SignatureAlgorithmHMAC_SHA256);
            $new_handle = uniqid();
            $lifetime = $this->server_configuration_service->getPrivateAssociationLifetime();
            $issued = gmdate("Y-m-d H:i:s", time());
            $this->association_service->addAssociation($new_handle, $new_secret,OpenIdProtocol::SignatureAlgorithmHMAC_SHA256,$lifetime, $issued,IAssociation::TypePrivate);
            $response->setAssocHandle($new_handle);
            if (!empty($assoc_handle)) {
                $response->setInvalidateHandle($assoc_handle);
            }
            $association = $this->association_service->getAssociation($new_handle);
        } else {
            $response->setAssocHandle($assoc_handle);
        }
        OpenIdSignatureBuilder::build($context, $association->getMacFunction(), $association->getSecret(), $response);
        return $response;
    }

    /**
     * @return mixed
     */
    private function doConsentProcess(){
        //do consent process
        $this->mementoRequestService->saveCurrentRequest();
        $this->current_request_context->setStage(RequestContext::StageConsent);
        foreach ($this->extensions  as $ext) {
            $ext->parseRequest($this->current_request, $this->current_request_context);
        }
        return $this->auth_strategy->doConsent($this->current_request, $this->current_request_context);
    }


    private function doLogin(){
        //do login process
        foreach ($this->extensions  as $ext) {
            $ext->parseRequest($this->current_request, $this->current_request_context);
        }
        $this->mementoRequestService->saveCurrentRequest();
        return $this->auth_strategy->doLogin($this->current_request, $this->current_request_context);
    }

    private function checkTrustedSite(ITrustedSite $site){
        $policy = $site->getAuthorizationPolicy();

        switch ($policy) {
            case IAuthService::AuthorizationResponse_AllowForever:
            {

                foreach ($this->extensions  as $ext) {
                    $data = $ext->getTrustedData($this->current_request);
                    $this->current_request_context->setTrustedData($data);
                }

                $requested_data = $this->current_request_context->getTrustedData();
                $trusted_data   = $site->getData();
                $diff = array_diff($requested_data,$trusted_data);
                if(!count($diff)) //already approved request
                    return $this->doAssertion();
                else
                {
                    return $this->doConsentProcess();
                }
            }
                break;
            case IAuthService::AuthorizationResponse_DenyForever:
                // black listed site
                return new OpenIdIndirectGenericErrorResponse(sprintf(OpenIdErrorMessages::RealmNotAllowedByUserMessage, $site->getRealm()));
                break;
            default:
                throw new \Exception("Invalid Realm Policy");
                break;
        }
    }


    private function checkAuthorizationResponse($authorization_response){
        // check response
        $currentUser            = $this->authService->getCurrentUser();
        switch ($authorization_response) {
            case IAuthService::AuthorizationResponse_AllowForever:
            {
                foreach ($this->extensions  as $ext) {
                    $data = $ext->getTrustedData($this->current_request);
                    $this->current_request_context->setTrustedData($data);
                }
                $this->trusted_sites_service->addTrustedSite($currentUser, $this->current_request->getRealm(), IAuthService::AuthorizationResponse_AllowForever,$this->current_request_context->getTrustedData());
                return $this->doAssertion();
            }
                break;
            case IAuthService::AuthorizationResponse_AllowOnce:
                return $this->doAssertion();
                break;
            case IAuthService::AuthorizationResponse_DenyOnce:
            {
                return new OpenIdNonImmediateNegativeAssertion($this->current_request->getReturnTo());
            }
            break;
            case IAuthService::AuthorizationResponse_DenyForever:{
                $this->trusted_sites_service->addTrustedSite($currentUser, $this->current_request->getRealm(), IAuthService::AuthorizationResponse_DenyForever);
                return new OpenIdNonImmediateNegativeAssertion($this->current_request->getReturnTo());
            }
            break;
            default:
                throw new \Exception("Invalid Authorization response!");
            break;
        }
    }
    /**
     * @return OpenIdIndirectGenericErrorResponse|OpenIdNonImmediateNegativeAssertion|OpenIdPositiveAssertionResponse
     * @throws \Exception
     */
    private function doSetupMode(){
        if (!$this->authService->isUserLogged()) {
            return $this->doLogin();
        } else {
            //user already logged
            $currentUser            = $this->authService->getCurrentUser();
            $site                   = $this->trusted_sites_service->getTrustedSite($currentUser, $this->current_request->getRealm());
            $authorization_response = $this->authService->getUserAuthorizationResponse();

            if ($authorization_response == IAuthService::AuthorizationResponse_None) {
                if (!is_null($site)) {
                    return $this->checkTrustedSite($site);
                } else {
                    return $this->doConsentProcess();
                }
            } else {
                return $this->checkAuthorizationResponse($authorization_response);
            }
        }
    }

    /**
     * @return OpenIdImmediateNegativeAssertion|OpenIdIndirectGenericErrorResponse|OpenIdPositiveAssertionResponse
     */
    protected function doImmediateMode(){
        if (!$this->authService->isUserLogged()) {
            return new OpenIdImmediateNegativeAssertion;
        }
        $currentUser = $this->authService->getCurrentUser();
        $site        = $this->trusted_sites_service->getTrustedSite($currentUser, $this->current_request->getRealm());
        if (is_null($site)) {
            //need setup to continue
            return new OpenIdImmediateNegativeAssertion($this->current_request->getReturnTo());
        }
        $policy = $site->getAuthorizationPolicy();

        switch($policy){
            case IAuthService::AuthorizationResponse_DenyForever:
            {
                // black listed site by user
                return new OpenIdIndirectGenericErrorResponse(sprintf(OpenIdErrorMessages::RealmNotAllowedByUserMessage, $site->getRealm()));
            }
            break;
            case IAuthService::AuthorizationResponse_AllowForever:
            {
                foreach ($this->extensions  as $ext) {
                    $data = $ext->getTrustedData($this->current_request);
                    $this->current_request_context->setTrustedData($data);
                }
                $requested_data = $this->current_request_context->getTrustedData();
                $trusted_data   = $site->getData();
                $diff = array_diff($requested_data,$trusted_data);
                if(!count($diff)) //already approved request
                    return $this->doAssertion();
                else
                {
                    //need setup to continue
                    return new OpenIdImmediateNegativeAssertion($this->current_request->getReturnTo());
                }
            }
            break;
            default:
                return new OpenIdIndirectGenericErrorResponse(sprintf(OpenIdErrorMessages::RealmNotAllowedByUserMessage, $this->current_request->getRealm()));
            break;
        }
    }

    /**
     * @param OpenIdMessage $message
     * @return OpenIdImmediateNegativeAssertion|OpenIdIndirectGenericErrorResponse|OpenIdNonImmediateNegativeAssertion|OpenIdPositiveAssertionResponse
     * @throws \openid\exceptions\InvalidOpenIdAuthenticationRequestMode
     */
    protected function InternalHandle(OpenIdMessage $message)
    {
        $this->current_request = null;
        try
        {
            $this->current_request = new OpenIdAuthenticationRequest($message);

            if(!$this->current_request->IsValid())
                throw new InvalidOpenIdMessageException("OpenIdAuthenticationRequest is Invalid!");

            $this->current_request_context  = new RequestContext;
            $mode                           = $this->current_request->getMode();

            switch ($mode) {
                case OpenIdProtocol::SetupMode:
                {
                    return $this->doSetupMode();
                }
                break;
                case OpenIdProtocol::ImmediateMode:
                {
                    return $this->doImmediateMode();
                }
                break;
                default:
                    throw new InvalidOpenIdAuthenticationRequestMode;
                break;
            }
        }
        catch (InvalidOpenIdMessageException $ex) {
            $response  = new OpenIdIndirectGenericErrorResponse($ex->getMessage());
            if(!is_null($this->current_request)){
                $return_to = $this->current_request->getReturnTo();
                if(!empty($return_to))
                    $response->setReturnTo($return_to);
            }
            return $response;
        }
    }

    protected function CanHandle(OpenIdMessage $message)
    {
        return OpenIdAuthenticationRequest::IsOpenIdAuthenticationRequest($message);
    }
}