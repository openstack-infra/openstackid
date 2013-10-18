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
use openid\exceptions\OpenIdIndirectGenericErrorResponse;
use openid\helpers\OpenIdErrorMessages;
use openid\helpers\OpenIdCryptoHelper;
use openid\model\IAssociation;
use openid\responses\OpenIdPositiveAssertionResponse;
use openid\services\IServerConfigurationService;
use openid\helpers\OpenIdSignatureBuilder;
use openid\exceptions\InvalidOpenIdMessageException;

/**
 * Class OpenIdAuthenticationRequestHandler
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
    }


    private function doAssertion(OpenIdAuthenticationRequest $request, $extensions)
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
        $identity = $currentUser->getIdentifier();
        $response = new OpenIdPositiveAssertionResponse($op_endpoint, $identity, $identity, $request->getReturnTo());
        foreach ($extensions as $ext) {
            $ext->prepareResponse($request, $response, $context);
        }
        //check former assoc handle...
        $assoc_handle = $request->getAssocHandle();
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

    protected function InternalHandle(OpenIdMessage $message)
    {
        try
        {
            $request = new OpenIdAuthenticationRequest($message);
            $extensions = $this->server_extensions_service->getAllActiveExtensions();
            $context = new RequestContext;
            $mode = $request->getMode();
            switch ($mode) {
                case OpenIdProtocol::SetupMode:
                {
                    if (!$this->authService->isUserLogged()) {
                        //do login process
                        $context->setStage(RequestContext::StageLogin);
                        foreach ($extensions as $ext) {
                            $ext->parseRequest($request, $context);
                        }
                        $this->mementoRequestService->saveCurrentRequest();
                        return $this->auth_strategy->doLogin($request, $context);
                    } else {
                        //user already logged
                        $currentUser = $this->authService->getCurrentUser();
                        $site = $this->trusted_sites_service->getTrustedSite($currentUser, $request->getTrustedRoot());
                        $authorization_response = $this->authService->getUserAuthorizationResponse();
                        if ($authorization_response == IAuthService::AuthorizationResponse_None) {
                            if (is_null($site)) {
                                //do consent process
                                $this->mementoRequestService->saveCurrentRequest();
                                $context->setStage(RequestContext::StageConsent);
                                foreach ($extensions as $ext) {
                                    $ext->parseRequest($request, $context);
                                }
                                return $this->auth_strategy->doConsent($request, $context);
                            } else {
                                $policy = $site->getAuthorizationPolicy();
                                switch ($policy) {
                                    case IAuthService::AuthorizationResponse_AllowForever:
                                        return $this->doAssertion($request, $extensions);
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
                        } else {
                            // check response
                            switch ($authorization_response) {
                                case IAuthService::AuthorizationResponse_AllowForever:
                                    $this->trusted_sites_service->addTrustedSite($currentUser, $request->getTrustedRoot(), IAuthService::AuthorizationResponse_AllowForever);
                                    return $this->doAssertion($request, $extensions);
                                    break;
                                case IAuthService::AuthorizationResponse_AllowOnce:
                                    return $this->doAssertion($request, $extensions);
                                    break;
                                case IAuthService::AuthorizationResponse_DenyOnce:
                                    return new OpenIdNonImmediateNegativeAssertion;
                                    break;
                                case IAuthService::AuthorizationResponse_DenyForever:
                                    $this->trusted_sites_service->addTrustedSite($currentUser, $request->getTrustedRoot(), IAuthService::AuthorizationResponse_DenyForever);
                                    return new OpenIdNonImmediateNegativeAssertion;
                                    break;
                                default:
                                    throw new \Exception("Invalid Authorization response!");
                                    break;
                            }
                        }
                    }
                }
                    break;
                case OpenIdProtocol::ImmediateMode:
                {
                    if (!$this->authService->isUserLogged()) {
                        return new OpenIdImmediateNegativeAssertion;
                    }
                    $currentUser = $this->authService->getCurrentUser();
                    $site = $this->trusted_sites_service->getTrustedSite($currentUser, $request->getTrustedRoot());
                    if (is_null($site)) {
                        return new OpenIdImmediateNegativeAssertion;
                    }
                    $policy = $site->getAuthorizationPolicy();
                    if ($policy == IAuthService::AuthorizationResponse_DenyForever) {
                        // black listed site
                        return new OpenIdIndirectGenericErrorResponse(sprintf(OpenIdErrorMessages::RealmNotAllowedByUserMessage, $site->getRealm()));
                    }
                    return $this->doAssertion($request, $extensions);
                }
                    break;
                default:
                    throw new InvalidOpenIdAuthenticationRequestMode;
                    break;
            }
        }
        catch (InvalidOpenIdMessageException $ex) {
            return new OpenIdIndirectGenericErrorResponse($ex->getMessage());
        }
    }

    protected function CanHandle(OpenIdMessage $message)
    {
        return OpenIdAuthenticationRequest::IsOpenIdAuthenticationRequest($message);
    }
}