<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:39 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid;

use openid\exceptions\InvalidOpenIdMessageMode;
use openid\helpers\OpenIdErrorMessages;

/**
 * Class OpenIdMessage
 * Implements a base OpenId Message
 * @package openid
 */
class OpenIdMessage implements \ArrayAccess
{

    protected $container = array();

    public function __construct(array $values)
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
            return $this->container[OpenIdProtocol::param($param, "_")];
        if (isset($this->container[OpenIdProtocol::param($param, ".")])) {
            return $this->container[OpenIdProtocol::param($param, ".")];
        }
        return null;
    }

    public function isValid()
    {
        $ns = $this->getParam(OpenIdProtocol::OpenIDProtocol_NS);
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
}