<?php namespace OpenId;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use OpenId\Exceptions\InvalidOpenIdMessageMode;
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\Requests\OpenIdMessageMemento;
use Utils\Http\HttpMessage;
/**
 * Class OpenIdMessage
 * Implements a base OpenId Message
 * @package OpenId
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

    /**
     * @return string
     */
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

    /**
     * @return bool
     */
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

    /**
     * @param string $mode
     * @return $this
     * @throws InvalidOpenIdMessageMode
     */
    protected function setMode($mode)
    {
        if (!OpenIdProtocol::isValidMode($mode))
            throw new InvalidOpenIdMessageMode(sprintf(OpenIdErrorMessages::InvalidOpenIdMessageModeMessage, $mode));
        $this->container[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)] = $mode;
        return $this;
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