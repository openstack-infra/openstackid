<?php

namespace openid\handlers;

use openid\helpers\OpenIdErrorMessages;
use openid\OpenIdMessage;
use utils\services\ILogService;
use utils\services\ICheckPointService;
use openid\exceptions\InvalidOpenIdMessageException;

/**
 * Class OpenIdMessageHandler
 * Abstract OpenId Message Handler
 * Implements Chain of Responsibility Pattern
 * @package openid\handlers
 */
abstract class OpenIdMessageHandler
{

	/**
	 * @var OpenIdMessageHandler
	 */
	protected $successor;
	/**
	 * @var OpenIdMessage
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
        $this->log_service->warning_msg(sprintf(OpenIdErrorMessages::UnhandledMessage, $message->toString()));
        $ex = new InvalidOpenIdMessageException(sprintf(OpenIdErrorMessages::UnhandledMessage, $message->toString()));
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