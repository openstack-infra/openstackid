<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 10:41 AM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\model;


interface IAssociation {

    const TypePrivate = "Private";
    const TypeSession = "Session";

    public function getMacFunction();
    public function setMacFunction($mac_function);

    public function getSecret();
    public function setSecret($secret);

    public function getLifetime();
    public function setLifetime($lifetime);

    public function getIssued();
    public function setIssued($issued);

    public function getType();
    public function setType($type);

    public function IsExpired();

}