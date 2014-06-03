<?php

namespace openid\model;

/**
 * Interface IAssociation
 * @package openid\model
 */
interface IAssociation {

    const TypePrivate = 1;
    const TypeSession = 2;

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

    public function getRealm();

    public function setRealm($realm);

    public function IsExpired();

    public function getRemainingLifetime();

	public function getHandle();

}