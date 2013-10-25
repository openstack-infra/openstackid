<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 12:16 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\requests;
use openid\OpenIdMessage;

abstract class OpenIdRequest {

   protected $message;

   public function __construct(OpenIdMessage $message){
       $this->message = $message;
   }

   public function getMessage(){
       return $this->message;
   }

   public function getMode(){
        return $this->message->getMode();
   }

   abstract public function IsValid();

   public function getParam($param){
       return $this->message[$param];
   }
}