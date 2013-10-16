<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:41 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\handlers;


use openid\OpenIdMessage;
use Whoops\Example\Exception;

abstract class OpenIdMessageHandler {

    protected  $successor;

    public function __construct($successor){
        $this->successor=$successor;
    }

    public function HandleMessage(OpenIdMessage $message){
        if($this->CanHandle($message)){
            //handle request
            return $this->InternalHandle($message);
        }
        else if(isset($this->successor) && !null($this->successor))
        {
            return $this->successor->HandleMessage($message);
        }
        throw new Exception("WTF?");
    }

    abstract protected function InternalHandle(OpenIdMessage $message);
    abstract protected function CanHandle(OpenIdMessage $message);
}