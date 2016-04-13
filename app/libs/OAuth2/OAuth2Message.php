<?php namespace OAuth2;
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
use Utils\Http\HttpMessage;
use OAuth2\Requests\OAuth2RequestMemento;
/**
 * Class OAuth2Message
 * @package OAuth2
 */
class OAuth2Message extends HttpMessage
{
    /**
     * OAuth2Message constructor.
     * @param array $values
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);
    }

    /**
     * @return string
     */
    public function toString()
    {
        $string = var_export($this->container, true);
        return $string;
    }

    /**
     * @param string $param
     * @return null|mixed
     */
    public function getParam($param)
    {
        return isset($this->container[$param])? $this->container[$param] : null;
    }

    /**
     * @param string $param
     * @param mixed $value
     * @return $this
     */
    public function setParam($param, $value)
    {
        $this->container[$param] = $value;
        return $this;
    }

    /**
     * @return OAuth2RequestMemento
     */
    public function createMemento(){
        return OAuth2RequestMemento::buildFromRequest($this);
    }

    /**
     * @param OAuth2RequestMemento $memento
     * @return $this
     */
    public function setMemento(OAuth2RequestMemento $memento){
        $this->container = $memento->getState();
        return $this;
    }

    /**
     * @param OAuth2RequestMemento $memento
     * @return OAuth2Message
     */
    static public function buildFromMemento(OAuth2RequestMemento $memento){
        $msg = new self;
        $msg->setMemento($memento);
        return $msg;
    }

}