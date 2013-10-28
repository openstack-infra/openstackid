<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:43 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\handlers;

use openid\exceptions\InvalidOpenIdMessageException;
use openid\OpenIdMessage;
use openid\requests\OpenIdAssociationSessionRequest;
use openid\responses\OpenIdDirectGenericErrorResponse;
use openid\services\ILogService;
use openid\handlers\factories\SessionAssociationRequestFactory;
/**
 * Class OpenIdSessionAssociationRequestHandler
 * Implements http://openid.net/specs/openid-authentication-2_0.html#associations
 * @package openid\handlers
 */
class OpenIdSessionAssociationRequestHandler extends OpenIdMessageHandler{



    public function __construct(ILogService $log ,$successor){
        parent::__construct($successor,$log);
    }

    protected function InternalHandle(OpenIdMessage $message){
        $this->current_request = null;
        try{

            $this->current_request = SessionAssociationRequestFactory::buildRequest($message);

            if(!$this->current_request->IsValid())
                throw new InvalidOpenIdMessageException("Association Session Request is Invalid!");

            $strategy = SessionAssociationRequestFactory::buildSessionAssociationStrategy($message);
            return $strategy->handle();

        }
        catch (InvalidOpenIdMessageException $ex) {
            $response  = new OpenIdDirectGenericErrorResponse($ex->getMessage());
            $this->log->error($ex);
            return $response;
        }
    }


    /**
     * @param OpenIdMessage $message
     * @return OpenIdAssociationSessionRequest
     */


    protected function CanHandle(OpenIdMessage $message)
    {
        $res = OpenIdAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message);
        return $res;
    }
}