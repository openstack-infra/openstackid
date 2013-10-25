<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:44 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\handlers;


use openid\OpenIdMessage;
use openid\requests\OpenIdCheckAuthenticationRequest;
use openid\exceptions\InvalidOpenIdMessageException;
use openid\responses\OpenIdDirectGenericErrorResponse;
use openid\services\IAssociationService;
use openid\services\INonceService;
use openid\model\IAssociation;
use openid\exceptions\ReplayAttackException;
use openid\responses\contexts\ResponseContext;
use openid\helpers\OpenIdSignatureBuilder;
use openid\responses\OpenIdPositiveAssertionResponse;
use openid\responses\OpenIdCheckAuthenticationResponse;

class OpenIdCheckAuthenticationRequestHandler extends OpenIdMessageHandler{


    private $association_service;
    private $nonce_service;
    private $current_request;

    public function __construct(IAssociationService $association_service,
                                INonceService $nonce_service,
                                $successor)
    {
        parent::__construct($successor);
        $this->association_service = $association_service;
        $this->nonce_service = $nonce_service;
    }


    protected function InternalHandle(OpenIdMessage $message){
        $this->current_request = null;
        try
        {
            $this->current_request = new OpenIdCheckAuthenticationRequest($message);

            if(!$this->current_request->IsValid())
                throw new InvalidOpenIdMessageException("OpenIdCheckAuthenticationRequest is Invalid!");

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
            $stored_assoc  = $this->association_service->getAssociation($claimed_assoc);

            if(is_null($stored_assoc) || $stored_assoc->getType()!=IAssociation::TypePrivate)
                throw new InvalidOpenIdMessageException("OpenIdCheckAuthenticationRequest is Invalid!");

            $claimed_nonce             = $this->current_request->getNonce();
            $claimed_sig               = $this->current_request->getSig();
            $claimed_op_endpoint       = $this->current_request->getOPEndpoint();
            $claimed_identity          = $this->current_request->getClaimedId();
            $claimed_invalidate_handle = $this->current_request->getInvalidateHandle();

            if(!is_null($claimed_invalidate_handle) && !empty($claimed_invalidate_handle)){
                $invalidate_stored_assoc  = $this->association_service->getAssociation($claimed_invalidate_handle);
                if(!is_null($invalidate_stored_assoc)){
                    $claimed_invalidate_handle = null;
                }
            }

            $this->nonce_service->markNonceAsInvalid($claimed_nonce,$claimed_sig);




            $res = OpenIdSignatureBuilder::verify($this->current_request, $stored_assoc->getMacFunction(), $stored_assoc->getSecret(),$claimed_sig);
            //delete association
            $this->association_service->deleteAssociation($claimed_assoc);
            $is_valid = 'false';
            if($res){
                //assertion is valid
                $is_valid = 'true';
            }
            return new OpenIdCheckAuthenticationResponse($is_valid,$claimed_invalidate_handle);
        }
        catch(ReplayAttackException $rEx){
            $response  = new OpenIdDirectGenericErrorResponse($rEx->getMessage());
            return $response;
        }
        catch (InvalidOpenIdMessageException $ex) {
            $response  = new OpenIdDirectGenericErrorResponse($ex->getMessage());
            return $response;
        }
    }

    protected  function CanHandle(OpenIdMessage $message)
    {
        $res = OpenIdCheckAuthenticationRequest::IsOpenIdCheckAuthenticationRequest($message);
        return $res;
    }


}