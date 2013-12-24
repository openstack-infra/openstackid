<?php
namespace oauth2\requests;
use oauth2\OAuth2Message;

abstract class OAuth2Request  extends OAuth2Message {

    protected $message;

    public function __construct(OAuth2Message $msg)
    {
        $this->message = $msg;
    }

    public function getMessage(){
        return $this->message;
    }

    public function getParam($param)
    {
        return $this->message->getParam($param);
    }

    public function toString()
    {
        $string = $this->message->toString();
        return $string;
    }

    public abstract function isValid();
} 