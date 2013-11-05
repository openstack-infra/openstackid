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

    public function __construct($successor, ILogService $log)
    {
        $this->successor = $successor;
        $this->log = $log;
    }

    /**
     * Implements chain of responsibility logic, if current handler could
     * manage the current message then do it, if not, then pass msg to next sibling
     * @param OpenIdMessage $message
     * @return mixed
     * @throws \openid\exceptions\InvalidOpenIdMessageException
     */
    public function HandleMessage(OpenIdMessage $message)
    {
        if ($this->CanHandle($message)) {
            //handle request
            return $this->InternalHandle($message);
        } else if (isset($this->successor) && !is_null($this->successor)) {
            return $this->successor->HandleMessage($message);
        }
        $this->log->warning_msg(sprintf(OpenIdErrorMessages::UnhandledMessage, $message->toString()));
        throw new InvalidOpenIdMessageException(sprintf(OpenIdErrorMessages::UnhandledMessage, $message->toString()));
    }

    /**
     * Returns true if current message could be managed by this handler
     * false otherwise.
     * @param OpenIdMessage $message
     * @return bool
     */
    abstract protected function CanHandle(OpenIdMessage $message);

    /**
     * Handler specific logic
     * @param OpenIdMessage $message
     * @return mixed
     */
    abstract protected function InternalHandle(OpenIdMessage $message);
}