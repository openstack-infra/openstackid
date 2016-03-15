<?php

namespace openid;

use openid\exceptions\InvalidOpenIdMessageMode;
use openid\helpers\OpenIdErrorMessages;
use utils\http\HttpMessage;
use openid\requests\OpenIdMessageMemento;

/**
 * Class OpenIdMessage
 * Implements a base OpenId Message
 * @package openid
 */
class OpenIdMessage extends HttpMessage
{

    /**
     * @param array $values
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);
    }

    public function getMode()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_Mode);
    }

    /**
     * @param OpenIDProtocol_ * $param
     * @return string
     */
    public function getParam($param)
    {
        if (isset($this->container[OpenIdProtocol::param($param, "_")]))
        {
            return $this->container[OpenIdProtocol::param($param, "_")];
        }

        if (isset($this->container[OpenIdProtocol::param($param, ".")]))
        {
            return $this->container[OpenIdProtocol::param($param, ".")];
        }
        return null;
    }

    public function isValid()
    {
        $ns   = $this->getParam(OpenIdProtocol::OpenIDProtocol_NS);
        $mode = $this->getParam(OpenIdProtocol::OpenIDProtocol_Mode);
        if (!is_null($ns)
            && $ns == OpenIdProtocol::OpenID2MessageType
            && !is_null($mode)
        ) {
            return true;
        }
        return false;
    }

    public function toString()
    {
        $string = var_export($this->container, true);
        return $string;
    }

    protected function setMode($mode)
    {
        if (!OpenIdProtocol::isValidMode($mode))
            throw new InvalidOpenIdMessageMode(sprintf(OpenIdErrorMessages::InvalidOpenIdMessageModeMessage, $mode));
        $this->container[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)] = $mode;;
    }

    /**
     * @return OpenIdMessageMemento
     */
    public function createMemento()
    {
        return OpenIdMessageMemento::buildFromRequest($this);
    }

    /**
     * @param OpenIdMessageMemento $memento
     * @return $this
     */
    public function setMemento(OpenIdMessageMemento $memento)
    {
        $this->container = $memento->getState();
        return $this;
    }

    /**
     * @param OpenIdMessageMemento $memento
     * @return OpenIdMessage
     */
    static public function buildFromMemento(OpenIdMessageMemento $memento)
    {
        $msg = new self;
        $msg->setMemento($memento);
        return $msg;
    }
}