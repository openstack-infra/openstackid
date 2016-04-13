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
use OpenId\Exceptions\InvalidOpenIdMessageException;
use OpenId\Exceptions\InvalidSessionTypeException;
use OpenId\Handlers\factories\SessionAssociationRequestFactory;
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\OpenIdMessage;
use OpenId\Requests\OpenIdAssociationSessionRequest;
use OpenId\Responses\OpenIdAssociationSessionUnsuccessfulResponse;
use OpenId\Responses\OpenIdDirectGenericErrorResponse;
use Utils\Services\ILogService;
use Utils\Services\ICheckPointService;
/**
 * Class OpenIdSessionAssociationRequestHandler
 * Implements @see http://openid.net/specs/openid-authentication-2_0.html#associations
 * @package OpenId\Handlers
 */
final class OpenIdSessionAssociationRequestHandler extends OpenIdMessageHandler
{

    /**
     * OpenIdSessionAssociationRequestHandler constructor.
     * @param ILogService $log
     * @param ICheckPointService $checkpoint_service
     * @param ICheckPointService $successor
     */
    public function __construct
    (
        ILogService $log,
        ICheckPointService $checkpoint_service,
        $successor
    )
    {
        parent::__construct($successor, $log,$checkpoint_service);
    }

    /**
     * @param OpenIdMessage $message
     * @return OpenIdAssociationSessionUnsuccessfulResponse|OpenIdDirectGenericErrorResponse
     */
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
            $this->log_service->warning($inv_session_ex);
            if(!is_null($this->current_request))
                $this->log_service->warning_msg("current request: ".$this->current_request);
            return $response;
        } catch (InvalidAssociationTypeException $inv_assoc_ex) {
            $this->checkpoint_service->trackException($inv_assoc_ex);
            $response = new OpenIdAssociationSessionUnsuccessfulResponse($inv_assoc_ex->getMessage());
            $this->log_service->warning($inv_assoc_ex);
            if(!is_null($this->current_request))
                $this->log_service->warning_msg("current request: ".$this->current_request);
            return $response;
        } catch (InvalidOpenIdMessageException $inv_msg_ex) {
            $response = new OpenIdDirectGenericErrorResponse($inv_msg_ex->getMessage());
            $this->checkpoint_service->trackException($inv_msg_ex);
            $this->log_service->warning($inv_msg_ex);
            if(!is_null($this->current_request))
                $this->log_service->warning_msg("current request: ".$this->current_request);
            return $response;
        } catch (Exception $ex) {
            $this->checkpoint_service->trackException($ex);
            $response = new OpenIdDirectGenericErrorResponse('Server Error');
            $this->log_service->error($ex);
            if(!is_null($this->current_request))
                $this->log_service->warning_msg("current request: ".$this->current_request);
            return $response;
        }
    }

    /**
     * @param OpenIdMessage $message
     * @return bool
     */
    protected function canHandle(OpenIdMessage $message) {
        return OpenIdAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message);
    }
}