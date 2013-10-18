<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 6:08 PM
 * To change this template use File | Settings | File Templates.
 */
use openid\model\IAssociation;

class OpenIdAssociation extends Eloquent implements  IAssociation{

    protected $table = 'openid_associations';
    public $timestamps = false;

    public function getMacFunction()
    {
        return $this->mac_function;
    }

    public function setMacFunction($mac_function)
    {
        // TODO: Implement setMacFunction() method.
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setSecret($secret)
    {
        // TODO: Implement setSecret() method.
    }

    public function getLifetime()
    {
        return $this->lifetime;
    }

    public function setLifetime($lifetime)
    {
        // TODO: Implement setLifetime() method.
    }

    public function getIssued()
    {
        return $this->issued;
    }

    public function setIssued($issued)
    {
        // TODO: Implement setIssued() method.
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        // TODO: Implement setType() method.
    }

    public function IsExpired()
    {
        // TODO: Implement IsExpired() method.
    }
}