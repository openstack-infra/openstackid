<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:44 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\handlers;


use openid\exceptions\InvalidNonce;
use openid\model\OpenIdNonce;
use openid\OpenIdMessage;
use openid\requests\OpenIdCheckAuthenticationRequest;
use openid\exceptions\InvalidOpenIdMessageException;
use openid\responses\OpenIdDirectGenericErrorResponse;
use openid\services\IAssociationService;
use openid\services\ILogService;
use openid\services\INonceService;
use openid\model\IAssociation;
use openid\exceptions\ReplayAttackException;
use openid\helpers\OpenIdSignatureBuilder;
use openid\responses\OpenIdCheckAuthenticationResponse;
use openid\exceptions\InvalidAssociationTypeException;

class OpenIdCheckAuthenticationRequestHandler extends OpenIdMessageHandler{


    private $association_service;
    private $nonce_service;

    public function __construct(IAssociationService $association_service,
                                INonceService $nonce_service,
                                ILogService $log,
                                $successor)
    {
        parent::__construct($successor,$log);
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
            $claimed_nonce           = new OpenIdNonce($this->current_request->getNonce());

            if(!$this->nonce_service->lockNonce($claimed_nonce))
                throw new ReplayAttackException(sprintf("nonce %s already used on a formed request!",$claimed_nonce->getRawFormat()));
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
                throw new InvalidAssociationTypeException(sprintf("invalid association type requested"));


            $claimed_realm             = $this->current_request->getRealm();
            $claimed_sig               = $this->current_request->getSig();
            $claimed_invalidate_handle = $this->current_request->getInvalidateHandle();

            if(!is_null($claimed_invalidate_handle) && !empty($claimed_invalidate_handle)){
                $invalidate_stored_assoc  = $this->association_service->getAssociation($claimed_invalidate_handle);
                if(!is_null($invalidate_stored_assoc)){
                    $claimed_invalidate_handle = null;
                }
            }

            $this->nonce_service->markNonceAsInvalid($claimed_nonce,$claimed_sig,$claimed_realm);

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
        catch(InvalidAssociationTypeException $inv_assoc_ex){
            $this->log->warning($inv_assoc_ex);
            $response  = new OpenIdDirectGenericErrorResponse($inv_assoc_ex->getMessage());
            return $response;
        }
        catch(ReplayAttackException $replay_ex){
            $this->log->warning($replay_ex);
            $response  = new OpenIdDirectGenericErrorResponse($replay_ex->getMessage());
            return $response;
        }
        catch(InvalidNonce $inv_nonce_ex){
            $this->log->error($inv_nonce_ex);
            $response  = new OpenIdDirectGenericErrorResponse($inv_nonce_ex->getMessage());
            return $response;
        }
        catch (InvalidOpenIdMessageException $inv_msg_ex) {
            $this->log->error($inv_msg_ex);
            $response  = new OpenIdDirectGenericErrorResponse($inv_msg_ex->getMessage());
            return $response;
        }
        catch(\Exception $ex){
            $this->log->error($ex);
            return new OpenIdDirectGenericErrorResponse("Server Error");
        }
    }

    protected  function CanHandle(OpenIdMessage $message)
    {
        $res = OpenIdCheckAuthenticationRequest::IsOpenIdCheckAuthenticationRequest($message);
        return $res;
    }


}