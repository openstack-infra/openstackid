<?php

namespace openid\requests;

use openid\OpenIdMessage;

abstract class OpenIdRequest
{

    protected $message;

    public function __construct(OpenIdMessage $message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getMode()
    {
        return $this->message->getMode();
    }

    abstract public function IsValid();

    /**
     * @param OpenIDProtocol_ * $param
     * @return string
     */
    public function getParam($param)
    {
        return $this->message->getParam($param);
    }
}