<?php

namespace openid\handlers;

use Exception;
use openid\exceptions\InvalidAssociationTypeException;
use openid\exceptions\InvalidOpenIdAuthenticationRequestMode;
use openid\exceptions\InvalidOpenIdMessageException;
use openid\exceptions\OpenIdInvalidRealmException;
use openid\exceptions\ReplayAttackException;
use openid\helpers\AssocHandleGenerator;
use openid\helpers\OpenIdCryptoHelper;
use openid\helpers\OpenIdErrorMessages;
use openid\helpers\OpenIdSignatureBuilder;
use openid\model\IAssociation;
use openid\OpenIdMessage;
use openid\OpenIdProtocol;
use openid\requests\contexts\RequestContext;
use openid\requests\OpenIdAuthenticationRequest;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdImmediateNegativeAssertion;
use openid\responses\OpenIdIndirectGenericErrorResponse;
use openid\responses\OpenIdNonImmediateNegativeAssertion;
use openid\responses\OpenIdPositiveAssertionResponse;
use openid\services\IAssociationService;
use openid\services\IAuthService;
use openid\services\ILogService;
use openid\services\IMementoOpenIdRequestService;
use openid\services\INonceService;
use openid\services\IServerConfigurationService;
use openid\services\IServerExtensionsService;
use openid\services\ITrustedSitesService;

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
    private $current_request_context;
    private $nonce_service;

    public function __construct(IAuthService $authService,
                                IMementoOpenIdRequestService $mementoRequestService,
                                IOpenIdAuthenticationStrategy $auth_strategy,
                                IServerExtensionsService $server_extensions_service,
                                IAssociationService $association_service,
                                ITrustedSitesService $trusted_sites_service,
                                IServerConfigurationService $server_configuration_service,
                                INonceService $nonce_service,
                                ILogService $log,
                                $successor)
    {
        parent::__construct($successor, $log);
        $this->authService = $authService;
        $this->mementoRequestService = $mementoRequestService;
        $this->auth_strategy = $auth_strategy;
        $this->server_extensions_service = $server_extensions_service;
        $this->association_service = $association_service;
        $this->trusted_sites_service = $trusted_sites_service;
        $this->server_configuration_service = $server_configuration_service;
        $this->extensions = $this->server_extensions_service->getAllActiveExtensions();
        $this->nonce_service = $nonce_service;
    }

    /**
     * @param OpenIdMessage $message
     * @return OpenIdImmediateNegativeAssertion|OpenIdIndirectGenericErrorResponse|OpenIdNonImmediateNegativeAssertion|OpenIdPositiveAssertionResponse
     * @throws \openid\exceptions\InvalidOpenIdAuthenticationRequestMode
     */
    protected function internalHandle(OpenIdMessage $message)
    {
        $this->current_request = null;
        try {
            $this->current_request = new OpenIdAuthenticationRequest($message);

            if (!$this->current_request->isValid())
                throw new InvalidOpenIdMessageException(OpenIdErrorMessages::InvalidOpenIdAuthenticationRequestMessage);

            $this->current_request_context = new RequestContext;
            $mode = $this->current_request->getMode();

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
                    throw new InvalidOpenIdAuthenticationRequestMode(sprintf(OpenIdErrorMessages::InvalidAuthenticationRequestModeMessage, $mode));
                    break;
            }
        } catch (InvalidAssociationTypeException $inv_assoc_type) {
            $this->checkpoint_service->trackException($inv_assoc_type);
            $this->log->warning($inv_assoc_type);
            return new OpenIdIndirectGenericErrorResponse($inv_assoc_type->getMessage(), null, null, $this->current_request);
        } catch (OpenIdInvalidRealmException $inv_realm_ex) {
            $this->checkpoint_service->trackException($inv_realm_ex);
            $this->log->error($inv_realm_ex);
            return new OpenIdIndirectGenericErrorResponse($inv_realm_ex->getMessage(), null, null, $this->current_request);
        } catch (ReplayAttackException $replay_ex) {
            $this->checkpoint_service->trackException($replay_ex);
            $this->log->error($replay_ex);
            return new OpenIdIndirectGenericErrorResponse($replay_ex->getMessage(), null, null, $this->current_request);
        } catch (InvalidOpenIdMessageException $inv_msg_ex) {
            $this->checkpoint_service->trackException($inv_msg_ex);
            $this->log->error($inv_msg_ex);
            return new OpenIdIndirectGenericErrorResponse($inv_msg_ex->getMessage(), null, null, $this->current_request);
        } catch (Exception $ex) {
            $this->checkpoint_service->trackException($ex);
            $this->log->error($ex);
            return new OpenIdIndirectGenericErrorResponse("Server Error", null, null, $this->current_request);
        }
    }

    /**
     * @return OpenIdIndirectGenericErrorResponse|OpenIdNonImmediateNegativeAssertion|OpenIdPositiveAssertionResponse
     * @throws \Exception
     */
    private function doSetupMode()
    {
        if (!$this->authService->isUserLogged()) {
            return $this->doLogin();
        } else {
            //user already logged
            $currentUser = $this->authService->getCurrentUser();
            if (!$this->current_request->isIdentitySelectByOP()) {
                $current_claimed_id = $this->current_request->getClaimedId();
                $current_identity = $this->current_request->getIdentity();

                // check is claimed identity match with current one
                // if not logs out and do re login
                $current_user = $this->authService->getCurrentUser();

                if (is_null($current_user))
                    throw new \Exception("User not set!");

                $current_owned_identity = $this->server_configuration_service->getUserIdentityEndpointURL($current_user->getIdentifier());

                if ($current_claimed_id != $current_owned_identity && $current_identity != $current_owned_identity) {
                    $this->log->warning_msg(sprintf(OpenIdErrorMessages::AlreadyExistSessionMessage, $current_owned_identity, $current_identity));
                    $this->authService->logout();
                    return $this->doLogin();
                }
            }

            $authorization_response = $this->authService->getUserAuthorizationResponse();

            if ($authorization_response == IAuthService::AuthorizationResponse_None) {
                $this->current_request_context->cleanTrustedData();
                foreach ($this->extensions as $ext) {
                    $data = $ext->getTrustedData($this->current_request);
                    $this->current_request_context->setTrustedData($data);
                }
                $requested_data = $this->current_request_context->getTrustedData();
                $sites = $this->trusted_sites_service->getTrustedSites($currentUser, $this->current_request->getRealm(), $requested_data);

                if (!is_null($sites) && count($sites) > 0) {
                    $site   = $sites[0];
                    $policy = $site->getAuthorizationPolicy();
                    switch ($policy) {
                        case IAuthService::AuthorizationResponse_AllowForever:
                        {
                            return $this->doAssertion();
                        }
                        break;
                        case IAuthService::AuthorizationResponse_DenyForever:
                            // black listed site
                            return new OpenIdIndirectGenericErrorResponse(sprintf(OpenIdErrorMessages::RealmNotAllowedByUserMessage, $site->getRealm()), null, null, $this->current_request);
                            break;
                        default:
                            throw new \Exception("Invalid Realm Policy");
                            break;
                    }
                } else {
                    return $this->doConsentProcess();
                }

            } else {
                return $this->checkAuthorizationResponse($authorization_response);
            }
        }
    }

    /**
     * @return mixed
     */
    private function doLogin()
    {
        //do login process
        foreach ($this->extensions as $ext) {
            $ext->parseRequest($this->current_request, $this->current_request_context);
        }
        $this->mementoRequestService->saveCurrentRequest();
        return $this->auth_strategy->doLogin($this->current_request, $this->current_request_context);
    }


    /**
     * Create Positive Identity Assertion
     * implements http://openid.net/specs/openid-authentication-2_0.html#positive_assertions
     * @return OpenIdPositiveAssertionResponse
     * @throws InvalidAssociationTypeException
     */
    private function doAssertion()
    {

        $currentUser = $this->authService->getCurrentUser();
        $context = new ResponseContext;

        //initial signature params
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint));
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm));
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo));
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Nonce));
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocHandle));
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId));
        $context->addSignParam(OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity));

        $op_endpoint = $this->server_configuration_service->getOPEndpointURL();
        $identity = $this->server_configuration_service->getUserIdentityEndpointURL($currentUser->getIdentifier());
        $nonce = $this->nonce_service->generateNonce();
        $realm = $this->current_request->getRealm();
        $response = new OpenIdPositiveAssertionResponse($op_endpoint, $identity, $identity, $this->current_request->getReturnTo(), $nonce->getRawFormat(), $realm);


        foreach ($this->extensions as $ext) {
            $ext->prepareResponse($this->current_request, $response, $context);
        }

        //check former assoc handle...
        $assoc_handle = $this->current_request->getAssocHandle();
        $association = $this->association_service->getAssociation($assoc_handle);

        if (empty($assoc_handle) || is_null($association)) {
            // if not present or if it already void then enter on dumb mode
            $new_secret = OpenIdCryptoHelper::generateSecret(OpenIdProtocol::SignatureAlgorithmHMAC_SHA256);
            $new_handle = AssocHandleGenerator::generate();
            $lifetime = $this->server_configuration_service->getConfigValue("Private.Association.Lifetime");
            $issued = gmdate("Y-m-d H:i:s", time());
            //create private association ...
            $this->association_service->addAssociation($new_handle, $new_secret, OpenIdProtocol::SignatureAlgorithmHMAC_SHA256, $lifetime, $issued, IAssociation::TypePrivate, $realm);
            $response->setAssocHandle($new_handle);
            if (!empty($assoc_handle)) {
                $response->setInvalidateHandle($assoc_handle);
            }
            $association = $this->association_service->getAssociation($new_handle);
        } else {
            if ($association->getType() != IAssociation::TypeSession)
                throw new InvalidAssociationTypeException(OpenIdErrorMessages::InvalidAssociationTypeMessage);
            $response->setAssocHandle($assoc_handle);
        }

        //create signature ...
        OpenIdSignatureBuilder::build($context, $association->getMacFunction(), $association->getSecret(), $response);
        /*
         * To prevent replay attacks, the OP MUST NOT issue more than one verification response for each
         * authentication response it had previously issued. An authentication response and its matching
         * verification request may be identified by their "openid.response_nonce" values.
         * so associate $nonce with signature and realm
         */
        $this->nonce_service->associateNonce($nonce, $response->getSig(), $realm);
        $this->mementoRequestService->clearCurrentRequest();
        return $response;
    }

    /**
     * @return mixed
     */
    private function doConsentProcess()
    {
        //do consent process
        $this->mementoRequestService->saveCurrentRequest();
        foreach ($this->extensions as $ext) {
            $ext->parseRequest($this->current_request, $this->current_request_context);
        }
        return $this->auth_strategy->doConsent($this->current_request, $this->current_request_context);
    }

    /**
     * @param $authorization_response
     * @return OpenIdNonImmediateNegativeAssertion|OpenIdPositiveAssertionResponse
     * @throws \Exception
     */
    private function checkAuthorizationResponse($authorization_response)
    {
        // check response
        $currentUser = $this->authService->getCurrentUser();
        switch ($authorization_response) {
            case IAuthService::AuthorizationResponse_AllowForever:
            {

                $this->current_request_context->cleanTrustedData();
                foreach ($this->extensions as $ext) {
                    $data = $ext->getTrustedData($this->current_request);
                    $this->current_request_context->setTrustedData($data);
                }

                $this->trusted_sites_service->addTrustedSite($currentUser, $this->current_request->getRealm(), IAuthService::AuthorizationResponse_AllowForever, $this->current_request_context->getTrustedData());
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
            case IAuthService::AuthorizationResponse_DenyForever:
            {
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
     * @return OpenIdImmediateNegativeAssertion|OpenIdIndirectGenericErrorResponse|OpenIdPositiveAssertionResponse
     */
    protected function doImmediateMode()
    {
        if (!$this->authService->isUserLogged()) {
            return new OpenIdImmediateNegativeAssertion($this->current_request->getReturnTo());
        }
        $currentUser = $this->authService->getCurrentUser();

        $this->current_request_context->cleanTrustedData();
        foreach ($this->extensions as $ext) {
            $data = $ext->getTrustedData($this->current_request);
            $this->current_request_context->setTrustedData($data);
        }

        $requested_data = $this->current_request_context->getTrustedData();

        $sites = $this->trusted_sites_service->getTrustedSites($currentUser, $this->current_request->getRealm(), $requested_data);

        if (is_null($sites) || count($sites) == 0) {
            //need setup to continue
            return new OpenIdImmediateNegativeAssertion($this->current_request->getReturnTo());
        }
        $site   = $sites[0];
        $policy = $site->getAuthorizationPolicy();

        switch ($policy) {
            case IAuthService::AuthorizationResponse_DenyForever:
            {
                // black listed site by user
                return new OpenIdIndirectGenericErrorResponse(sprintf(OpenIdErrorMessages::RealmNotAllowedByUserMessage, $site->getRealm()), null, null, $this->current_request);
            }
                break;
            case IAuthService::AuthorizationResponse_AllowForever:
            {
                return $this->doAssertion();
            }
            break;
            default:
                return new OpenIdIndirectGenericErrorResponse(sprintf(OpenIdErrorMessages::RealmNotAllowedByUserMessage, $this->current_request->getRealm()), null, null, $this->current_request);
                break;
        }
    }

    /**
     * @param OpenIdMessage $message
     * @return bool
     */
    protected function canHandle(OpenIdMessage $message)
    {
        $res = OpenIdAuthenticationRequest::IsOpenIdAuthenticationRequest($message);
        return $res;
    }
}