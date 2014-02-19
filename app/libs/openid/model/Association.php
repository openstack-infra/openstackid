<?php

namespace openid\model;

/**
 * Class Association
 * @package openid\model
 */
class Association implements IAssociation {

	private $handle;
	private $secret;
	private $mac_function;
	private $lifetime;
	private $issued;
	private $type;
	private $realm;

	public function __construct($handle, $secret, $mac_function, $lifetime, $issued, $type, $realm){
		$this->handle =	$handle;
		$this->secret = $secret;
		$this->mac_function = $mac_function;
		$this->lifetime = $lifetime;
		$this->issued =  $issued;
		$this->type = $type;
		$this->realm = $realm;
	}

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

	public function getRealm()
	{
		return $this->realm;
	}

	public function setRealm($realm)
	{
		// TODO: Implement setRealm() method.
	}

	public function IsExpired()
	{
		// TODO: Implement IsExpired() method.
	}

	public function getRemainingLifetime()
	{
		// TODO: Implement getRemainingLifetime() method.
	}

	public function getHandle()
	{
		return $this->handle;
	}
}