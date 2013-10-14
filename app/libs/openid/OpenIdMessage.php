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

    protected $valid_modes;
    protected $container = array();

    const OpenID2MessageType="http://specs.openid.net/auth/2.0";

    public function __construct(array $values) {
        $this->valid_modes = array("checkid_setup","checkid_immediate","check_authentication","associate");
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

    public function IsValid(){
        if (isset($this->container["openid.ns"])
            && $this->container["openid.ns"] == self::OpenID2MessageType
            && isset($this->container["openid.mode"])
            && in_array($this->container["openid.mode"],$this->valid_modes)){
            return true;
        }
        return false;
    }
}