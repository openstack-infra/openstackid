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
use openid\services\ILogService;

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

    protected function InternalHandle(OpenIdMessage $message)
    {
        $this->current_request = null;
        try {

            $this->current_request = SessionAssociationRequestFactory::buildRequest($message);

            if (!$this->current_request->IsValid())
                throw new InvalidOpenIdMessageException(OpenIdErrorMessages::InvalidAssociationSessionRequest);

            $strategy = SessionAssociationRequestFactory::buildSessionAssociationStrategy($message);
            return $strategy->handle();
        } catch (InvalidSessionTypeException $inv_session_ex) {
            $response = new OpenIdAssociationSessionUnsuccessfulResponse($inv_session_ex->getMessage());
            $this->log->error($inv_session_ex);
            return $response;
        } catch (InvalidAssociationTypeException $inv_assoc_ex) {
            $response = new OpenIdAssociationSessionUnsuccessfulResponse($inv_assoc_ex->getMessage());
            $this->log->error($inv_assoc_ex);
            return $response;
        } catch (InvalidOpenIdMessageException $inv_msg_ex) {
            $response = new OpenIdDirectGenericErrorResponse($inv_msg_ex->getMessage());
            $this->log->error($inv_msg_ex);
            return $response;
        } catch (Exception $ex) {
            $response = new OpenIdDirectGenericErrorResponse('Server Error');
            $this->log->error($ex);
            return $response;
        }
    }

    /**
     * @param OpenIdMessage $message
     * @return bool
     */
    protected function CanHandle(OpenIdMessage $message)
    {
        $res = OpenIdAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message);
        return $res;
    }
}