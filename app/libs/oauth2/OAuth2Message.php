<?php
namespace oauth2;

use utils\http\HttpMessage;

class OAuth2Message extends HttpMessage
{
    public function __construct(array $values)
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

}