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

class OpenIdMessage implements \ArrayAccess {


    protected $container = array();

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
        return $this->container[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode,"_")];
    }

    protected function setMode($mode){
        if(!OpenIdProtocol::isValidMode($mode))
            throw new InvalidOpenIdMessageMode($mode);
        $this->container[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)]=$mode;;
    }

    public function IsValid(){
        if (isset($this->container[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS,"_")])
            && $this->container[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS,"_")] == OpenIdProtocol::OpenID2MessageType
            && isset($this->container[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode,"_")])){
            return true;
        }
        return false;
    }
}