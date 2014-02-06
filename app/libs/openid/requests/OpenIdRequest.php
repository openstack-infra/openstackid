<?php

namespace openid\requests;

use openid\OpenIdMessage;
use utils\services\ServiceLocator;
use utils\services\UtilsServiceCatalog;

abstract class OpenIdRequest
{

    protected $message;
    protected $log_service;

    public function __construct(OpenIdMessage $message)
    {
        $this->message     = $message;
        $this->log_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::LogService);
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

    public function toString()
    {
        $string = $this->message->toString();
        return $string;
    }
}