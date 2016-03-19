<?php

namespace openid\requests;

use openid\OpenIdMessage;

/**
 * Class OpenIdRequest
 * @package openid\requests
 */
abstract class OpenIdRequest
{
    /**
     * @var OpenIdMessage
     */
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

    abstract public function isValid();

    /**
     * @param OpenIDProtocol_ * $param
     * @return string
     */
    public function getParam($param)
    {
        return $this->message->getParam($param);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->message->__toString();
    }
}