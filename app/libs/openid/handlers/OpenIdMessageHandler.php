<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:41 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\handlers;

use openid\exceptions\InvalidOpenIdMessageException;
use openid\helpers\OpenIdErrorMessages;
use openid\OpenIdMessage;
use openid\services\ILogService;
use openid\services\OpenIdRegistry;
use openid\services\OpenIdServiceCatalog;

/**
 * Class OpenIdMessageHandler
 * Abstract OpenId Message Handler
 * Implements Chain of Responsibility Pattern
 * @package openid\handlers
 */
abstract class OpenIdMessageHandler
{

    protected $successor;
    protected $current_request;
    protected $log;
    protected $checkpoint_service;

    public function __construct($successor, ILogService $log)
    {
        $this->successor = $successor;
        $this->log = $log;
        $this->checkpoint_service = OpenIdRegistry::getInstance()->get(OpenIdServiceCatalog::CheckPointService);
    }

    /**
     * Implements chain of responsibility logic, if current handler could
     * manage the current message then do it, if not, then pass msg to next sibling
     * @param OpenIdMessage $message
     * @return mixed
     * @throws \openid\exceptions\InvalidOpenIdMessageException
     */
    public function handleMessage(OpenIdMessage $message)
    {
        if ($this->canHandle($message)) {
            //handle request
            return $this->internalHandle($message);
        } else if (isset($this->successor) && !is_null($this->successor)) {
            return $this->successor->HandleMessage($message);
        }
        $this->log->warning_msg(sprintf(OpenIdErrorMessages::UnhandledMessage, $message->toString()));
        $ex  = new InvalidOpenIdMessageException(sprintf(OpenIdErrorMessages::UnhandledMessage, $message->toString()));
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