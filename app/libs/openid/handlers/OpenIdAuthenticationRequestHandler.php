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
use openid\services\IMementoOpenIdRequestService;
use openid\services\INonceService;
use openid\services\IServerConfigurationService;
use openid\services\IServerExtensionsService;
use openid\services\ITrustedSitesService;
use openid\helpers\AssociationFactory;
use utils\services\IAuthService;
use utils\services\ILogService;
use utils\services\ICheckPointService;

/**
 * Class OpenIdAuthenticationRequestHandler
 * Implements
 * http://openid.net/specs/openid-authentication-2_0.html#requesting_authentication
 * http://openid.net/specs/openid-authentication-2_0.html#responding_to_authentication
 * @package openid\handlers
 */

class OpenIdAuthenticationRequestHandler extends OpenIdMessageHandler
{
    private $auth_service;
    private $memento_service;
    private $auth_strategy;
    private $server_extensions_service;
    private $association_service;
    private $trusted_sites_service;
    private $server_configuration_service;
    private $extensions;
    private $current_request_context;
    private $nonce_service;

    public function __construct(IAuthService $authService,
                                IMementoOpenIdRequestService $memento_service,
                                IOpenIdAuthenticationStrategy $auth_strategy,
                                IServerExtensionsService $server_extensions_service,
                                IAssociationService $association_service,
                                ITrustedSitesService $trusted_sites_service,
                                IServerConfigurationService $server_configuration_service,
                                INonceService $nonce_service,
                                ILogService $log,
                                ICheckPointService $checkpoint_service,
                                $successor)
    {
        parent::__construct($successor, $log,$checkpoint_service);
        $this->auth_service                 = $authService;
        $this->memento_service              = $memento_service;
        $this->auth_strategy                = $auth_strategy;
        $this->server_extensions_service    = $server_extensions_service;
        $this->association_service          = $association_service;
        $this->trusted_sites_service        = $trusted_sites_service;
        $this->server_configuration_service = $server_configuration_service;
        $this->extensions                   = $this->server_extensions_service->getAllActiveExtensions();
        $this->nonce_service                = $nonce_service;
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

            $this->current_request = new OpenIdAuthenticationRequest($message,$this->server_configuration_service->getUserIdentityEndpointURL('@identifier'));

            if (!$this->current_request->isValid()){
                throw new InvalidOpenIdMessageException(OpenIdErrorMessages::InvalidOpenIdAuthenticationRequestMessage);
            }

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
            $this->log_service->warning($inv_assoc_type);
            if(!is_null($this->current_request))
                $this->log_service->error_msg("current request: ".$this->current_request->toString());
            return new OpenIdIndirectGenericErrorResponse($inv_assoc_type->getMessage(), null, null, $this->current_request);
        } catch (OpenIdInvalidRealmException $inv_realm_ex) {
            $this->checkpoint_service->trackException($inv_realm_ex);
            $this->log_service->error($inv_realm_ex);
            if(!is_null($this->current_request))
                $this->log_service->error_msg("current request: ".$this->current_request->toString());
            return new OpenIdIndirectGenericErrorResponse($inv_realm_ex->getMessage(), null, null, $this->current_request);
        } catch (ReplayAttackException $replay_ex) {
            $this->checkpoint_service->trackException($replay_ex);
            $this->log_service->error($replay_ex);
            if(!is_null($this->current_request))
                $this->log_service->error_msg("current request: ".$this->current_request->toString());
            return new OpenIdIndirectGenericErrorResponse($replay_ex->getMessage(), null, null, $this->current_request);
        } catch (InvalidOpenIdMessageException $inv_msg_ex) {
            $this->checkpoint_service->trackException($inv_msg_ex);
            $this->log_service->error($inv_msg_ex);
            if(!is_null($this->current_request))
                $this->log_service->error_msg("current request: ".$this->current_request->toString());
            return new OpenIdIndirectGenericErrorResponse($inv_msg_ex->getMessage(), null, null, $this->current_request);
        } catch (Exception $ex) {
            $this->checkpoint_service->trackException($ex);
            $this->log_service->error($ex);
            if(!is_null($this->current_request))
                $this->log_service->error_msg("current request: ".$this->current_request->toString());
            return new OpenIdIndirectGenericErrorResponse("Server Error", null, null, $this->current_request);
        }
    }

    /**
     * @return OpenIdIndirectGenericErrorResponse|OpenIdNonImmediateNegativeAssertion|OpenIdPositiveAssertionResponse
     * @throws \Exception
     */
    private function doSetupMode()
    {

	    $authentication_response = $this->auth_service->getUserAuthenticationResponse();
	    if($authentication_response == IAuthService::AuthenticationResponse_Cancel){
		    //clear saved data ...
		    $this->memento_service->clearCurrentRequest();
		    $this->auth_service->clearUserAuthenticationResponse();
		    $this->auth_service->clearUserAuthorizationResponse();
		    return new OpenIdNonImmediateNegativeAssertion($this->current_request->getReturnTo());
	    }

	    if (!$this->auth_service->isUserLogged())
            return $this->doLogin();

        //user already logged
        $currentUser = $this->auth_service->getCurrentUser();

        if (!$this->current_request->isIdentitySelectByOP()) {

        $current_claimed_id = $this->current_request->getClaimedId();
        $current_identity   = $this->current_request->getIdentity();
        // check is claimed identity match with current one
        // if not logs out and do re login
        $current_user       = $this->auth_service->getCurrentUser();
        if (is_null($current_user))
            throw new Exception("User not set!");

            $current_owned_identity = $this->server_configuration_service->getUserIdentityEndpointURL($current_user->getIdentifier());

            if ($current_claimed_id != $current_owned_identity && $current_identity != $current_owned_identity) {
                $this->log_service->warning_msg(sprintf(OpenIdErrorMessages::AlreadyExistSessionMessage, $current_owned_identity, $current_identity));
                $this->auth_service->logout();
                return $this->doLogin();
            }
        }

        $authorization_response = $this->auth_service->getUserAuthorizationResponse();
        if ($authorization_response !== IAuthService::AuthorizationResponse_None)
            return $this->checkAuthorizationResponse($authorization_response);

        // $authorization_response is none ...
        $this->current_request_context->cleanTrustedData();
        foreach ($this->extensions as $ext) {
            $data = $ext->getTrustedData($this->current_request);
            $this->current_request_context->setTrustedData($data);
        }

        $requested_data = $this->current_request_context->getTrustedData();
        $sites          = $this->trusted_sites_service->getTrustedSites($currentUser, $this->current_request->getRealm(), $requested_data);
        //check trusted sites
        if (is_null($sites) || count($sites) == 0)
            return $this->doConsentProcess();
        //there are trusted sites ... check the former authorization decision
        $site   = $sites[0];
        $policy = $site->getAuthorizationPolicy();
        switch ($policy) {
                case IAuthService::AuthorizationResponse_AllowForever:
                {
                    //save former user choice on session
                    $this->auth_service->setUserAuthorizationResponse($policy);
                    return $this->doAssertion();
                }
                break;
                case IAuthService::AuthorizationResponse_DenyForever:
                    // black listed site
                    return new OpenIdIndirectGenericErrorResponse(sprintf(OpenIdErrorMessages::RealmNotAllowedByUserMessage, $site->getRealm()), null, null, $this->current_request);
                break;
                default:
                    throw new Exception("Invalid Realm Policy");
                break;
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
        $this->memento_service->saveCurrentRequest();
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

        $currentUser = $this->auth_service->getCurrentUser();
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
        $identity    = $this->server_configuration_service->getUserIdentityEndpointURL($currentUser->getIdentifier());
        $nonce       = $this->nonce_service->generateNonce();
        $realm       = $this->current_request->getRealm();

        $response    = new OpenIdPositiveAssertionResponse($op_endpoint, $identity, $identity, $this->current_request->getReturnTo(), $nonce->getRawFormat(), $realm);

        foreach ($this->extensions as $ext) {
            $ext->prepareResponse($this->current_request, $response, $context);
        }

        //check former assoc handle...

        if (is_null($assoc_handle = $this->current_request->getAssocHandle()) || is_null($association = $this->association_service->getAssociation($assoc_handle))) {
            //create private association ...
            $association = $this->association_service->addAssociation(AssociationFactory::getInstance()->buildPrivateAssociation($realm,$this->server_configuration_service->getConfigValue("Private.Association.Lifetime")));
            $response->setAssocHandle($association->getHandle());
            if (!empty($assoc_handle)) {
                $response->setInvalidateHandle($assoc_handle);
            }
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
        //do cleaning ...
        $this->memento_service->clearCurrentRequest();
        $this->auth_service->clearUserAuthorizationResponse();
        return $response;
    }

    /**
     * @return mixed
     */
    private function doConsentProcess()
    {
        //do consent process
        $this->memento_service->saveCurrentRequest();
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
        $currentUser = $this->auth_service->getCurrentUser();
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
                $this->memento_service->clearCurrentRequest();
                $this->auth_service->clearUserAuthorizationResponse();
                return new OpenIdNonImmediateNegativeAssertion($this->current_request->getReturnTo());
            }
            break;
            case IAuthService::AuthorizationResponse_DenyForever:
            {

                $this->current_request_context->cleanTrustedData();
                foreach ($this->extensions as $ext) {
                    $data = $ext->getTrustedData($this->current_request);
                    $this->current_request_context->setTrustedData($data);
                }

                $this->trusted_sites_service->addTrustedSite($currentUser, $this->current_request->getRealm(), IAuthService::AuthorizationResponse_DenyForever,$this->current_request_context->getTrustedData());
                $this->memento_service->clearCurrentRequest();
                $this->auth_service->clearUserAuthorizationResponse();

                return new OpenIdNonImmediateNegativeAssertion($this->current_request->getReturnTo());
            }
                break;
            default:
                $this->memento_service->clearCurrentRequest();
                $this->auth_service->clearUserAuthorizationResponse();
                throw new \Exception("Invalid Authorization response!");
                break;
        }
    }

    /**
     * @return OpenIdImmediateNegativeAssertion|OpenIdIndirectGenericErrorResponse|OpenIdPositiveAssertionResponse
     */
    protected function doImmediateMode()
    {
        if (!$this->auth_service->isUserLogged()) {
            return new OpenIdImmediateNegativeAssertion($this->current_request->getReturnTo());
        }

        $currentUser = $this->auth_service->getCurrentUser();

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
                //save former user choice on session
                $this->auth_service->setUserAuthorizationResponse($policy);
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