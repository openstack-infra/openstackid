<?php

namespace utils\http;

/**
 * Class HttpMessage
 * @package utils\http
 */
class HttpMessage implements \ArrayAccess
{

    /**
     * @var array
     */
    protected $container = array();

    /**
     * @param array $values
     */
    public function __construct(array $values = array())
    {
        $this->container = $values;
    }

    /**
     * arrayaccess methods
     * */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}
