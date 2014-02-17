<?php

namespace openid\handlers;

use Exception;
use openid\exceptions\InvalidAssociationTypeException;
use openid\exceptions\InvalidNonce;
use openid\exceptions\InvalidOpenIdMessageException;
use openid\exceptions\ReplayAttackException;
use openid\helpers\OpenIdErrorMessages;
use openid\helpers\OpenIdSignatureBuilder;
use openid\model\IAssociation;
use openid\model\OpenIdNonce;
use openid\OpenIdMessage;
use openid\requests\OpenIdCheckAuthenticationRequest;
use openid\responses\OpenIdCheckAuthenticationResponse;
use openid\responses\OpenIdDirectGenericErrorResponse;
use openid\services\IAssociationService;
use openid\services\INonceService;
use utils\services\ILogService;
use utils\services\ICheckPointService;
use utils\services\IServerConfigurationService;

/**
 * Class OpenIdCheckAuthenticationRequestHandler
 * Implements http://openid.net/specs/openid-authentication-2_0.html#check_auth
 * Verifying Directly with the OpenID Provider
 * To have the signature verification performed by the OP, the Relying Party sends a direct request to the OP.
 * To verify the signature, the OP uses a private association that was generated when it issued
 * the positive assertion.
 * @package openid\handlers
 */
class OpenIdCheckAuthenticationRequestHandler extends OpenIdMessageHandler
{

    private $association_service;
    private $nonce_service;
	private $configuration_service;

	/**
	 * @param IAssociationService         $association_service
	 * @param INonceService               $nonce_service
	 * @param ILogService                 $log_service
	 * @param ICheckPointService          $checkpoint_service
	 * @param IServerConfigurationService $configuration_service
	 * @param                             $successor
	 */
	public function __construct(IAssociationService $association_service,
                                INonceService $nonce_service,
                                ILogService $log_service,
                                ICheckPointService $checkpoint_service,
	                            IServerConfigurationService $configuration_service,
                                $successor)
    {
        parent::__construct($successor, $log_service, $checkpoint_service);

        $this->association_service   = $association_service;
        $this->nonce_service         = $nonce_service;
	    $this->configuration_service = $configuration_service;
    }

    protected function internalHandle(OpenIdMessage $message)
    {
        $this->current_request = null;
        try {
            $this->current_request = new OpenIdCheckAuthenticationRequest($message);

            if (!$this->current_request->isValid())
                throw new InvalidOpenIdMessageException(OpenIdErrorMessages::InvalidOpenIdCheckAuthenticationRequestMessage);


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

            $claimed_nonce = new OpenIdNonce($this->current_request->getNonce());

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

            $res = OpenIdSignatureBuilder::verify($this->current_request, $stored_assoc->getMacFunction(), $stored_assoc->getSecret(), $claimed_sig);
            //delete association
            $this->association_service->deleteAssociation($claimed_assoc);
            $is_valid = $res ? 'true':'false';
            return new OpenIdCheckAuthenticationResponse($is_valid, $claimed_invalidate_handle);

        } catch (InvalidAssociationTypeException $inv_assoc_ex) {
            $this->checkpoint_service->trackException($inv_assoc_ex);
            $this->log_service->warning($inv_assoc_ex);
            $response = new OpenIdDirectGenericErrorResponse($inv_assoc_ex->getMessage());
            if(!is_null($this->current_request))
                $this->log_service->error_msg("current request: ".$this->current_request->toString());
            return $response;
        } catch (ReplayAttackException $replay_ex) {
            $this->checkpoint_service->trackException($replay_ex);
            $this->log_service->warning($replay_ex);
            $response = new OpenIdDirectGenericErrorResponse($replay_ex->getMessage());
            if(!is_null($this->current_request))
                $this->log_service->error_msg("current request: ".$this->current_request->toString());
            return $response;
        } catch (InvalidNonce $inv_nonce_ex) {
            $this->checkpoint_service->trackException($inv_nonce_ex);
            $this->log_service->error($inv_nonce_ex);
            $response = new OpenIdDirectGenericErrorResponse($inv_nonce_ex->getMessage());
            if(!is_null($this->current_request))
                $this->log_service->error_msg("current request: ".$this->current_request->toString());
            return $response;
        } catch (InvalidOpenIdMessageException $inv_msg_ex) {
            $this->checkpoint_service->trackException($inv_msg_ex);
            $this->log_service->error($inv_msg_ex);
            $response = new OpenIdDirectGenericErrorResponse($inv_msg_ex->getMessage());
            if(!is_null($this->current_request))
                $this->log_service->error_msg("current request: ".$this->current_request->toString());
            return $response;
        } catch (Exception $ex) {
            $this->checkpoint_service->trackException($ex);
            $this->log_service->error($ex);
            if(!is_null($this->current_request))
                $this->log_service->error_msg("current request: ".$this->current_request->toString());
            return new OpenIdDirectGenericErrorResponse("Server Error");
        }
    }

    protected function canHandle(OpenIdMessage $message)
    {
        $res = OpenIdCheckAuthenticationRequest::IsOpenIdCheckAuthenticationRequest($message);
        return $res;
    }
}