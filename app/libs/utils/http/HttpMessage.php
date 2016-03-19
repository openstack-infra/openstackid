<?php

namespace utils\http;

use utils\IPHelper;

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
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * @param mixed $offset
     * @return null
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $string = var_export(array_merge(array('from_ip' => IPHelper::getUserIp()), $this->container), true);
        return (string)$string;
    }
}
