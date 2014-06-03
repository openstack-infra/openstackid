<?php

use openid\model\IAssociation;

/**
 * Class OpenIdAssociation
 */
class OpenIdAssociation extends Eloquent implements IAssociation
{

    public $timestamps = false;
    protected $table = 'openid_associations';

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
        return intval($this->lifetime);
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

    public function getRealm()
    {
        return $this->realm;
    }

    public function setRealm($realm)
    {
        // TODO: Implement setRealm() method.
    }

    public function getRemainingLifetime()
    {
        $created_at = new DateTime($this->issued, new DateTimeZone("UTC"));
        $created_at->add(new DateInterval('PT' . intval($this->lifetime) . 'S'));
        $now        = new DateTime(gmdate("Y-m-d H:i:s", time()), new DateTimeZone("UTC"));
        //check validity...
        if ($now > $created_at)
            return -1;
        $seconds = abs($created_at->getTimestamp() - $now->getTimestamp());;
        return $seconds;
    }

	public function getHandle()
	{
		return $this->identifier;
	}
}