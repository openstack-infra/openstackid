<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:39 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid;


class OpenIdMessage implements \ArrayAccess {


    protected $container = array();

    const OpenID2MessageType="http://specs.openid.net/auth/2.0";
    const ModeType = "openid_mode";
    const NSType   = "openid_ns";

    public function __construct(array $values) {
        $this->container = $values;
    }

    /**
     * arrayaccess methods
     * */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }


    public function getMode(){
        return $this->container[self::ModeType];
    }

    public function IsValid(){
        if (isset($this->container[self::NSType])
            && $this->container[self::NSType] == self::OpenID2MessageType
            && isset($this->container[self::ModeType])){
            return true;
        }
        return false;
    }
}