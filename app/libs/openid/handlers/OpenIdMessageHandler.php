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
use openid\OpenIdMessage;
use \Exception;
use openid\services\ILogService;

abstract class OpenIdMessageHandler {

    protected  $successor;
    protected  $current_request;
    protected  $log;


    public function __construct($successor, ILogService $log){
        $this->successor = $successor;
        $this->log       = $log;
    }

    public function HandleMessage(OpenIdMessage $message){
        if($this->CanHandle($message)){
            //handle request
            return $this->InternalHandle($message);
        }
        else if(isset($this->successor) && !is_null($this->successor))
        {
            return $this->successor->HandleMessage($message);
        }
        $this->log->warning_msg( sprintf("unhandled message %s", $message->toString()));
        throw new InvalidOpenIdMessageException( sprintf("unhandled message %s", $message->toString()));
    }

    abstract protected function InternalHandle(OpenIdMessage $message);
    abstract protected function CanHandle(OpenIdMessage $message);
}