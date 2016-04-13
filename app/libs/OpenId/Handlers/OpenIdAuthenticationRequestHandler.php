<?php namespace OpenId\Handlers;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use Exception;
use OpenId\Exceptions\InvalidAssociationTypeException;
use OpenId\Exceptions\InvalidOpenIdAuthenticationRequestMode;
use OpenId\Exceptions\InvalidOpenIdMessageException;
use OpenId\Exceptions\OpenIdInvalidRealmException;
use OpenId\Exceptions\ReplayAttackException;
use OpenId\Helpers\AssociationFactory;
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\Helpers\OpenIdSignatureBuilder;
use OpenId\Models\IAssociation;
use OpenId\OpenIdMessage;
use OpenId\OpenIdProtocol;
use OpenId\Requests\Contexts\RequestContext;
use OpenId\Responses\Contexts\ResponseContext;
use OpenId\Requests\OpenIdAuthenticationRequest;
use OpenId\Responses\OpenIdImmediateNegativeAssertion;
use OpenId\Responses\OpenIdIndirectGenericErrorResponse;
use OpenId\Responses\OpenIdNonImmediateNegativeAssertion;
use OpenId\Responses\OpenIdPositiveAssertionResponse;
use OpenId\Responses\OpenIdResponse;
use OpenId\Services\IAssociationService;
use OpenId\Services\IMementoOpenIdSerializerService;
use OpenId\Services\INonceService;
use OpenId\Services\IServerConfigurationService;
use OpenId\Services\IServerExtensionsService;
use OpenId\Services\ITrustedSitesService;
use Utils\Services\IAuthService;
use Utils\Services\ICheckPointService;
use Utils\Services\ILogService;
/**
 * Class OpenIdAuthenticationRequestHandler
 * Implements
 * @see http://openid.net/specs/openid-authentication-2_0.html#requesting_authentication
 * @see http://openid.net/specs/openid-authentication-2_0.html#responding_to_authentication
 * @package OpenId\Handlers
 */
final class OpenIdAuthenticationRequestHandler extends OpenIdMessageHandler
{
    /**
     * @var IAuthService
     */
    private $auth_service;
    /**
     * @var IMementoOpenIdSerializerService
     */
    private $memento_service;
    /**
     * @var IOpenIdAuthenticationStrategy
     */
    private $auth_strategy;
    /**
     * @var IServerExtensionsService
     */
    private $server_extensions_service;
    /**
     * @var IAssociationService
     */
    private $association_service;
    /**
     * @var ITrustedSitesService
     */
    private $trusted_sites_service;
    /**
     * @var IServerConfigurationService
     */
    private $server_configuration_service;
    /**
     * @var
     */
    private $extensions;
    /**
     * @var
     */
    private $current_request_context;
    /**
     * @var INonceService
     */
    private $nonce_service;

    /**
     * @param IAuthService $authService
     * @param IMementoOpenIdSerializerService $memento_service
     * @param IOpenIdAuthenticationStrategy $auth_strategy
     * @param IServerExtensionsService $server_extensions_service
     * @param IAssociationService $association_service
     * @param ITrustedSitesService $trusted_sites_service
     * @param IServerConfigurationService $server_configuration_service
     * @param INonceService $nonce_service
     * @param ILogService $log
     * @param ICheckPointService $checkpoint_service
     * @param $successor
     */
    public function __construct(
        IAuthService $authService,
        IMementoOpenIdSerializerService $memento_service,
        IOpenIdAuthenticationStrategy $auth_strategy,
        IServerExtensionsService $server_extensions_service,
        IAssociationService $association_service,
        ITrustedSitesService $trusted_sites_service,
        IServerConfigurationService $server_configuration_service,
        INonceService $nonce_service,
        ILogService $log,
        ICheckPointService $checkpoint_service,
        $successor
    ) {
        parent::__construct($successor, $log, $checkpoint_service);

        $this->auth_service = $authService;
        $this->memento_service = $memento_service;
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
     * @return OpenIdResponse
     * @throws InvalidOpenIdAuthenticationRequestMode
     */
    protected function internalHandle(OpenIdMessage $message)
    {
        $this->current_request = null;

        try {

            $this->current_request = new OpenIdAuthenticationRequest(
                $message,
                $this->server_configuration_service->getUserIdentityEndpointURL('@identifier')
            );

            if (!$this->current_request->isValid()) {
                throw new InvalidOpenIdMessageException(OpenIdErrorMessages::InvalidOpenIdAuthenticationRequestMessage);
            }

            $this->current_request_context = new RequestContext;
            $mode = $this->current_request->getMode();

            switch ($mode) {
                case OpenIdProtocol::SetupMode: {
                    return $this->doSetupMode();
                }
                    break;
                case OpenIdProtocol::ImmediateMode: {
                    return $this->doImmediateMode();
                }
                    break;
                default:
                    throw new InvalidOpenIdAuthenticationRequestMode(sprintf(OpenIdErrorMessages::InvalidAuthenticationRequestModeMessage,
                        $mode));
                    break;
            }
        } catch (InvalidAssociationTypeException $inv_assoc_type) {
            $this->checkpoint_service->trackException($inv_assoc_type);
            $this->log_service->warning($inv_assoc_type);
            if (!is_null($this->current_request)) {
                $this->log_service->warning_msg("current request: ".$this->current_request);
            }
            return new OpenIdIndirectGenericErrorResponse($inv_assoc_type->getMessage(), null, null,$this->current_request);
        } catch (OpenIdInvalidRealmException $inv_realm_ex) {
            $this->checkpoint_service->trackException($inv_realm_ex);
            $this->log_service->warning($inv_realm_ex);
            if (!is_null($this->current_request)) {
                $this->log_service->warning_msg("current request: ".$this->current_request);
            }
            return new OpenIdIndirectGenericErrorResponse($inv_realm_ex->getMessage(), null, null, $this->current_request);
        } catch (ReplayAttackException $replay_ex) {
            $this->checkpoint_service->trackException($replay_ex);
            $this->log_service->warning($replay_ex);
            if (!is_null($this->current_request)) {
                $this->log_service->warning_msg("current request: ".$this->current_request);;
            }
            return new OpenIdIndirectGenericErrorResponse($replay_ex->getMessage(), null, null, $this->current_request);
        } catch (InvalidOpenIdMessageException $inv_msg_ex) {
            $this->checkpoint_service->trackException($inv_msg_ex);
            $this->log_service->warning($inv_msg_ex);
            if (!is_null($this->current_request)) {
                $this->log_service->warning_msg("current request: ".$this->current_request);;
            }
            return new OpenIdIndirectGenericErrorResponse($inv_msg_ex->getMessage(), null, null, $this->current_request);
        } catch (Exception $ex) {
            $this->checkpoint_service->trackException($ex);
            $this->log_service->error($ex);
            if (!is_null($this->current_request)) {
                $this->log_service->warning_msg("current request: ".$this->current_request);;
            }
            return new OpenIdIndirectGenericErrorResponse("Server Error", null, null, $this->current_request);
        }
    }

    /**
     * @return OpenIdResponse
     * @throws Exception
     */
    private function doSetupMode()
    {

        $authentication_response = $this->auth_service->getUserAuthenticationResponse();
        if ($authentication_response == IAuthService::AuthenticationResponse_Cancel) {
            //clear saved data ...
            $this->memento_service->forget();
            $this->auth_service->clearUserAuthenticationResponse();
            $this->auth_service->clearUserAuthorizationResponse();

            return new OpenIdNonImmediateNegativeAssertion($this->current_request->getReturnTo());
        }

        if (!$this->auth_service->isUserLogged()) {
            return $this->doLogin();
        }

        //user already logged
        $currentUser = $this->auth_service->getCurrentUser();

        if (!$this->current_request->isIdentitySelectByOP()) {

            $current_claimed_id = $this->current_request->getClaimedId();
            $current_identity = $this->current_request->getIdentity();
            // check is claimed identity match with current one
            // if not logs out and do re login
            $current_user = $this->auth_service->getCurrentUser();
            if (is_null($current_user)) {
                throw new Exception("User not set!");
            }

            $current_owned_identity = $this->server_configuration_service->getUserIdentityEndpointURL($current_user->getIdentifier());

            if ($current_claimed_id != $current_owned_identity && $current_identity != $current_owned_identity) {
                $this->log_service->warning_msg(sprintf(OpenIdErrorMessages::AlreadyExistSessionMessage,
                    $current_owned_identity, $current_identity));
                $this->auth_service->logout();

                return $this->doLogin();
            }
        }

        $authorization_response = $this->auth_service->getUserAuthorizationResponse();
        if ($authorization_response !== IAuthService::AuthorizationResponse_None) {
            return $this->checkAuthorizationResponse($authorization_response);
        }

        // $authorization_response is none ...
        $this->current_request_context->cleanTrustedData();
        foreach ($this->extensions as $ext) {
            $data = $ext->getTrustedData($this->current_request);
            $this->current_request_context->setTrustedData($data);
        }

        $requested_data = $this->current_request_context->getTrustedData();
        $sites          = $this->trusted_sites_service->getTrustedSites(
                                $currentUser,
                                $this->current_request->getRealm(),
                                $requested_data
                          );

        //check trusted sites
        if (is_null($sites) || count($sites) == 0) {
            return $this->doConsentProcess();
        }
        //there are trusted sites ... check the former authorization decision
        $site = $sites[0];
        $policy = $site->getAuthorizationPolicy();
        switch ($policy) {
            case IAuthService::AuthorizationResponse_AllowForever: {
                //save former user choice on session
                $this->auth_service->setUserAuthorizationResponse($policy);

                return $this->doAssertion();
            }
                break;
            case IAuthService::AuthorizationResponse_DenyForever:
                // black listed site
                return new OpenIdIndirectGenericErrorResponse(sprintf(OpenIdErrorMessages::RealmNotAllowedByUserMessage,
                    $site->getRealm()), null, null, $this->current_request);
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

        $this->memento_service->serialize($this->current_request->getMessage()->createMemento());

        return $this->auth_strategy->doLogin($this->current_request, $this->current_request_context);
    }


    /**
     * Create Positive Identity Assertion
     * implements @see http://openid.net/specs/openid-authentication-2_0.html#positive_assertions
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
        $identity = $this->server_configuration_service->getUserIdentityEndpointURL($currentUser->getIdentifier());
        $nonce = $this->nonce_service->generateNonce();
        $realm = $this->current_request->getRealm();

        $response = new OpenIdPositiveAssertionResponse($op_endpoint, $identity, $identity,
            $this->current_request->getReturnTo(), $nonce->getRawFormat(), $realm);

        foreach ($this->extensions as $ext) {
            $ext->prepareResponse($this->current_request, $response, $context);
        }

        //check former assoc handle...

        if (is_null($assoc_handle = $this->current_request->getAssocHandle()) || is_null($association = $this->association_service->getAssociation($assoc_handle))) {
            //create private association ...
            $association = $this->association_service->addAssociation
            (
                AssociationFactory::getInstance()->buildPrivateAssociation
                (
                    $realm,
                    $this->server_configuration_service->getConfigValue("Private.Association.Lifetime")
                )
            );

            $response->setAssocHandle($association->getHandle());

            if (!empty($assoc_handle)) {
                $response->setInvalidateHandle($assoc_handle);
            }
        } else {
            if ($association->getType() != IAssociation::TypeSession) {
                throw new InvalidAssociationTypeException(OpenIdErrorMessages::InvalidAssociationTypeMessage);
            }
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
        $this->memento_service->forget();
        $this->auth_service->clearUserAuthorizationResponse();

        return $response;
    }

    /**
     * @return mixed
     */
    private function doConsentProcess()
    {
        //do consent process
        $this->memento_service->serialize($this->current_request->getMessage()->createMemento());

        foreach ($this->extensions as $ext) {
            $ext->parseRequest($this->current_request, $this->current_request_context);
        }

        return $this->auth_strategy->doConsent($this->current_request, $this->current_request_context);
    }

    /**
     * @param $authorization_response
     * @return OpenIdResponse
     * @throws Exception
     */
    private function checkAuthorizationResponse($authorization_response)
    {
        // check response
        $currentUser = $this->auth_service->getCurrentUser();
        switch ($authorization_response) {
            case IAuthService::AuthorizationResponse_AllowForever: {

                $this->current_request_context->cleanTrustedData();
                foreach ($this->extensions as $ext) {
                    $data = $ext->getTrustedData($this->current_request);
                    $this->current_request_context->setTrustedData($data);
                }

                $this->trusted_sites_service->addTrustedSite($currentUser, $this->current_request->getRealm(),
                    IAuthService::AuthorizationResponse_AllowForever, $this->current_request_context->getTrustedData());

                return $this->doAssertion();
            }
                break;
            case IAuthService::AuthorizationResponse_AllowOnce:
                return $this->doAssertion();
                break;
            case IAuthService::AuthorizationResponse_DenyOnce: {
                $this->memento_service->forget();
                $this->auth_service->clearUserAuthorizationResponse();

                return new OpenIdNonImmediateNegativeAssertion($this->current_request->getReturnTo());
            }
                break;
            case IAuthService::AuthorizationResponse_DenyForever: {

                $this->current_request_context->cleanTrustedData();
                foreach ($this->extensions as $ext) {
                    $data = $ext->getTrustedData($this->current_request);
                    $this->current_request_context->setTrustedData($data);
                }

                $this->trusted_sites_service->addTrustedSite($currentUser, $this->current_request->getRealm(),
                    IAuthService::AuthorizationResponse_DenyForever, $this->current_request_context->getTrustedData());
                $this->memento_service->forget();
                $this->auth_service->clearUserAuthorizationResponse();

                return new OpenIdNonImmediateNegativeAssertion($this->current_request->getReturnTo());
            }
                break;
            default:
                $this->memento_service->forget();
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

        $sites = $this->trusted_sites_service->getTrustedSites($currentUser, $this->current_request->getRealm(),
            $requested_data);

        if (is_null($sites) || count($sites) == 0) {
            //need setup to continue
            return new OpenIdImmediateNegativeAssertion($this->current_request->getReturnTo());
        }
        $site = $sites[0];
        $policy = $site->getAuthorizationPolicy();

        switch ($policy) {
            case IAuthService::AuthorizationResponse_DenyForever: {
                // black listed site by user
                return new OpenIdIndirectGenericErrorResponse(sprintf(OpenIdErrorMessages::RealmNotAllowedByUserMessage,
                    $site->getRealm()), null, null, $this->current_request);
            }
                break;
            case IAuthService::AuthorizationResponse_AllowForever: {
                //save former user choice on session
                $this->auth_service->setUserAuthorizationResponse($policy);

                return $this->doAssertion();
            }
                break;
            default:
                return new OpenIdIndirectGenericErrorResponse(sprintf(OpenIdErrorMessages::RealmNotAllowedByUserMessage,
                    $this->current_request->getRealm()), null, null, $this->current_request);
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