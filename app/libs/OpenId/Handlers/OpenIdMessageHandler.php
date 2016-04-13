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
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\OpenIdMessage;
use OpenId\Exceptions\InvalidOpenIdMessageException;
use OpenId\Requests\OpenIdAuthenticationRequest;
use Utils\Services\ILogService;
use Utils\Services\ICheckPointService;
/**
 * Class OpenIdMessageHandler
 * Abstract OpenId Message Handler
 * Implements Chain of Responsibility Pattern
 * @package OpenId\Handlers
 */
abstract class OpenIdMessageHandler
{
	/**
	 * @var OpenIdMessageHandler
	 */
	protected $successor;
	/**
	 * @var OpenIdAuthenticationRequest
	 */
	protected $current_request;
	/**
	 * @var ILogService
	 */
	protected $log_service;
	/**
	 * @var ICheckPointService
	 */
	protected $checkpoint_service;

	/**
	 * @param                    $successor
	 * @param ILogService        $log_service
	 * @param ICheckPointService $checkpoint_service
	 */
	public function __construct($successor, ILogService $log_service, ICheckPointService $checkpoint_service)
    {
        $this->successor          = $successor;
        $this->log_service        = $log_service;
        $this->checkpoint_service = $checkpoint_service;
    }

    /**
     * Implements chain of responsibility logic, if current handler could
     * manage the current message then do it, if not, then pass msg to next sibling
     * @param OpenIdMessage $message
     * @return mixed
     * @throws InvalidOpenIdMessageException
     */
    public function handleMessage(OpenIdMessage $message)
    {
        if ($this->canHandle($message)) {
            //handle request
            return $this->internalHandle($message);
        } else if (isset($this->successor) && !is_null($this->successor)) {
            return $this->successor->handleMessage($message);
        }
        $this->log_service->warning_msg(sprintf(OpenIdErrorMessages::UnhandledMessage, $message));
        $ex = new InvalidOpenIdMessageException(sprintf(OpenIdErrorMessages::UnhandledMessage, $message));
        $this->checkpoint_service->trackException($ex);
        throw $ex;
    }

    /**
     * Returns true if current message could be managed by this handler
     * false otherwise.
     * @param OpenIdMessage $message
     * @return bool
     */
    abstract protected function canHandle(OpenIdMessage $message);

    /**
     * Handler specific logic
     * @param OpenIdMessage $message
     * @return mixed
     */
    abstract protected function internalHandle(OpenIdMessage $message);
}