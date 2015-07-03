<?php

namespace oauth2;

use utils\http\HttpMessage;
use oauth2\requests\OAuth2RequestMemento;

/**
 * Class OAuth2Message
 * @package oauth2
 */
class OAuth2Message extends HttpMessage
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);
    }

    public function toString()
    {
        $string = var_export($this->container, true);
        return $string;
    }

    public function getParam($param)
    {
        return isset($this->container[$param])?$this->container[$param]:null;
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