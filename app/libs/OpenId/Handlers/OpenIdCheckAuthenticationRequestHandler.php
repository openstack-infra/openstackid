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
use OpenId\Exceptions\InvalidNonce;
use OpenId\Exceptions\InvalidOpenIdMessageException;
use OpenId\Exceptions\ReplayAttackException;
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\Helpers\OpenIdSignatureBuilder;
use OpenId\Models\IAssociation;
use OpenId\Models\OpenIdNonce;
use OpenId\OpenIdMessage;
use OpenId\Requests\OpenIdCheckAuthenticationRequest;
use OpenId\Responses\OpenIdCheckAuthenticationResponse;
use OpenId\Responses\OpenIdDirectGenericErrorResponse;
use OpenId\Services\IAssociationService;
use OpenId\Services\INonceService;
use OpenId\services\IServerConfigurationService as IOpenIdServerConfigurationService;
use Utils\Services\ILogService;
use Utils\Services\ICheckPointService;
use Utils\Services\IServerConfigurationService;
/**
 * Class OpenIdCheckAuthenticationRequestHandler
 * Implements http://openid.net/specs/openid-authentication-2_0.html#check_auth
 * Verifying Directly with the OpenID Provider
 * To have the signature verification performed by the OP, the Relying Party sends a direct request to the OP.
 * To verify the signature, the OP uses a private association that was generated when it issued
 * the positive assertion.
 * @package OpenId\Handlers
 */
final class OpenIdCheckAuthenticationRequestHandler extends OpenIdMessageHandler
{
    /**
     * @var IAssociationService
     */
    private $association_service;
    /**
     * @var INonceService
     */
    private $nonce_service;
    /**
     * @var IServerConfigurationService
     */
	private $configuration_service;
    /**
     * @var IOpenIdServerConfigurationService
     */
	private $openid_configuration_service;

	/**
	 * @param IAssociationService               $association_service
	 * @param INonceService                     $nonce_service
	 * @param ILogService                       $log_service
	 * @param ICheckPointService                $checkpoint_service
	 * @param IServerConfigurationService       $configuration_service
	 * @param IOpenIdServerConfigurationService $openid_configuration_service
	 * @param                                   $successor
	 */
	public function __construct(IAssociationService $association_service,
                                INonceService $nonce_service,
                                ILogService $log_service,
                                ICheckPointService $checkpoint_service,
	                            IServerConfigurationService $configuration_service,
	                            IOpenIdServerConfigurationService $openid_configuration_service,
                                $successor)
    {
        parent::__construct($successor, $log_service, $checkpoint_service);

        $this->association_service          = $association_service;
        $this->nonce_service                = $nonce_service;
	    $this->configuration_service        = $configuration_service;
	    $this->openid_configuration_service = $openid_configuration_service;
    }

    /**
     * @param OpenIdMessage $message
     * @return OpenIdCheckAuthenticationResponse|OpenIdDirectGenericErrorResponse
     */
    protected function internalHandle(OpenIdMessage $message)
    {
        $this->current_request = null;
        try {

            $this->current_request = new OpenIdCheckAuthenticationRequest
            (
                $message,$this->openid_configuration_service->getOPEndpointURL()
            );

            if (!$this->current_request->isValid())
                throw new InvalidOpenIdMessageException
                (
                    OpenIdErrorMessages::InvalidOpenIdCheckAuthenticationRequestMessage
                );

            /**
             *  For verifying signatures an OP MUST only use private associations and MUST NOT
             *  use associations that have shared keys. If the verification request contains a handle
             * for a shared association, it means the Relying Party no longer knows the shared secret,
             * or an entity other than the RP (e.g. an attacker) has established this association with
             * the OP.
             * To prevent replay attacks, the OP MUST NOT issue more than one verification response for each
             * authentication response it had previously issued. An authentication response and its matching
             * verification request may be identified by their "openid.response_nonce" values.
             */

            $claimed_assoc = $this->current_request->getAssocHandle();
            $claimed_realm = $this->current_request->getRealm();
            $stored_assoc  = $this->association_service->getAssociation($claimed_assoc, $claimed_realm);

            if (is_null($stored_assoc) || $stored_assoc->getType() != IAssociation::TypePrivate)
                throw new InvalidAssociationTypeException(OpenIdErrorMessages::InvalidAssociationTypeMessage);

            $claimed_nonce = OpenIdNonce::fromValue($this->current_request->getNonce());

            if(!$claimed_nonce->isValid(intval($this->configuration_service->getConfigValue('Nonce.Lifetime'))))
                throw new InvalidNonce();

            $this->nonce_service->lockNonce($claimed_nonce);

            $claimed_sig               = $this->current_request->getSig();
            $claimed_invalidate_handle = $this->current_request->getInvalidateHandle();

            if (!is_null($claimed_invalidate_handle) && !empty($claimed_invalidate_handle)) {
                $invalidate_stored_assoc = $this->association_service->getAssociation($claimed_invalidate_handle);
                if (!is_null($invalidate_stored_assoc)) {
                    $claimed_invalidate_handle = null;
                }
            }

            $this->nonce_service->markNonceAsInvalid($claimed_nonce, $claimed_sig, $claimed_realm);

            $res = OpenIdSignatureBuilder::verify
            (
                $this->current_request,
                $stored_assoc->getMacFunction(),
                $stored_assoc->getSecret(),
                $claimed_sig
            );

            //delete association
            $this->association_service->deleteAssociation($claimed_assoc);
            $is_valid = $res ? 'true':'false';
            return new OpenIdCheckAuthenticationResponse($is_valid, $claimed_invalidate_handle);

        } catch (InvalidAssociationTypeException $inv_assoc_ex) {
            $this->checkpoint_service->trackException($inv_assoc_ex);
            $this->log_service->warning($inv_assoc_ex);
            $response = new OpenIdDirectGenericErrorResponse($inv_assoc_ex->getMessage());
            if(!is_null($this->current_request))
                $this->log_service->warning_msg("current request: ".$this->current_request);
            return $response;
        } catch (ReplayAttackException $replay_ex) {
            $this->checkpoint_service->trackException($replay_ex);
            $this->log_service->warning($replay_ex);
            $response = new OpenIdDirectGenericErrorResponse($replay_ex->getMessage());
            if(!is_null($this->current_request))
                $this->log_service->warning_msg("current request: ".$this->current_request);
            return $response;
        } catch (InvalidNonce $inv_nonce_ex) {
            $this->checkpoint_service->trackException($inv_nonce_ex);
            $this->log_service->error($inv_nonce_ex);
            $response = new OpenIdDirectGenericErrorResponse($inv_nonce_ex->getMessage());
            if(!is_null($this->current_request))
                $this->log_service->warning_msg("current request: ".$this->current_request);
            return $response;
        } catch (InvalidOpenIdMessageException $inv_msg_ex) {
            $this->checkpoint_service->trackException($inv_msg_ex);
            $this->log_service->error($inv_msg_ex);
            $response = new OpenIdDirectGenericErrorResponse($inv_msg_ex->getMessage());
            if(!is_null($this->current_request))
                $this->log_service->warning_msg("current request: ".$this->current_request);
            return $response;
        } catch (Exception $ex) {
            $this->checkpoint_service->trackException($ex);
            $this->log_service->error($ex);
            if(!is_null($this->current_request))
                $this->log_service->warning_msg("current request: ".$this->current_request);
            return new OpenIdDirectGenericErrorResponse("Server Error");
        }
    }

    /**
     * @param OpenIdMessage $message
     * @return bool
     */
    protected function canHandle(OpenIdMessage $message)
    {
        return OpenIdCheckAuthenticationRequest::IsOpenIdCheckAuthenticationRequest($message);
    }
}