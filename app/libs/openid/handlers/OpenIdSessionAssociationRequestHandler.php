<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:43 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\handlers;

use Exception;
use openid\exceptions\InvalidAssociationTypeException;
use openid\exceptions\InvalidOpenIdMessageException;
use openid\exceptions\InvalidSessionTypeException;
use openid\handlers\factories\SessionAssociationRequestFactory;
use openid\helpers\OpenIdErrorMessages;
use openid\OpenIdMessage;
use openid\requests\OpenIdAssociationSessionRequest;
use openid\responses\OpenIdAssociationSessionUnsuccessfulResponse;
use openid\responses\OpenIdDirectGenericErrorResponse;
use utils\services\ILogService;

/**
 * Class OpenIdSessionAssociationRequestHandler
 * Implements http://openid.net/specs/openid-authentication-2_0.html#associations
 * @package openid\handlers
 */
class OpenIdSessionAssociationRequestHandler extends OpenIdMessageHandler
{

    public function __construct(ILogService $log, $successor)
    {
        parent::__construct($successor, $log);
    }

    protected function internalHandle(OpenIdMessage $message)
    {
        $this->current_request = null;
        try {

            $this->current_request = SessionAssociationRequestFactory::buildRequest($message);

            if (!$this->current_request->isValid())
                throw new InvalidOpenIdMessageException(OpenIdErrorMessages::InvalidAssociationSessionRequest);

            $strategy = SessionAssociationRequestFactory::buildSessionAssociationStrategy($message);
            return $strategy->handle();
        } catch (InvalidSessionTypeException $inv_session_ex) {
            $this->checkpoint_service->trackException($inv_session_ex);
            $response = new OpenIdAssociationSessionUnsuccessfulResponse($inv_session_ex->getMessage());
            $this->log->error($inv_session_ex);
            if(!is_null($this->current_request))
                $this->log->error_msg("current request: ".$this->current_request->toString());
            return $response;
        } catch (InvalidAssociationTypeException $inv_assoc_ex) {
            $this->checkpoint_service->trackException($inv_assoc_ex);
            $response = new OpenIdAssociationSessionUnsuccessfulResponse($inv_assoc_ex->getMessage());
            $this->log->error($inv_assoc_ex);
            if(!is_null($this->current_request))
                $this->log->error_msg("current request: ".$this->current_request->toString());
            return $response;
        } catch (InvalidOpenIdMessageException $inv_msg_ex) {
            $response = new OpenIdDirectGenericErrorResponse($inv_msg_ex->getMessage());
            $this->checkpoint_service->trackException($inv_msg_ex);
            $this->log->error($inv_msg_ex);
            if(!is_null($this->current_request))
                $this->log->error_msg("current request: ".$this->current_request->toString());
            return $response;
        } catch (Exception $ex) {
            $this->checkpoint_service->trackException($ex);
            $response = new OpenIdDirectGenericErrorResponse('Server Error');
            $this->log->error($ex);
            if(!is_null($this->current_request))
                $this->log->error_msg("current request: ".$this->current_request->toString());
            return $response;
        }
    }

    /**
     * @param OpenIdMessage $message
     * @return bool
     */
    protected function canHandle(OpenIdMessage $message)
    {
        $res = OpenIdAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message);
        return $res;
    }
}