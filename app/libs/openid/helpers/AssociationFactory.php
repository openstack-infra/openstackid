<?php
namespace openid\helpers;

use openid\model\Association;
use openid\model\IAssociation;
use openid\OpenIdProtocol;

/**
 * Class AssociationFactory
 * Singleton Factory that creates OpenId Associations
 * @package openid\helpers
 */
final class AssociationFactory {

	private static $instance = null;

	private function __construct(){
	}

	public static function getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new AssociationFactory();
		}

		return self::$instance;
	}

	/**
	 * @param $realm
	 * @param $lifetime
	 * @return IAssociation
	 */
	public function buildPrivateAssociation($realm,$lifetime)
	{
		return $this->buildAssociation(IAssociation::TypePrivate,OpenIdProtocol::SignatureAlgorithmHMAC_SHA256,$lifetime,$realm);
	}

	/**
	 * @param $mac_function
	 * @param $lifetime
	 * @return IAssociation
	 */
	public function buildSessionAssociation($mac_function,$lifetime){
		return $this->buildAssociation(IAssociation::TypeSession,$mac_function,$lifetime,null);
	}

	/**
	 * @param $type
	 * @param $mac_function
	 * @param $lifetime
	 * @param $realm
	 * @return IAssociation
	 */
	private function buildAssociation($type,$mac_function,$lifetime,$realm){
		$new_secret           = OpenIdCryptoHelper::generateSecret($mac_function);
		$new_handle           = AssocHandleGenerator::generate();
		$expires_in           = intval($lifetime);
		$issued               = gmdate("Y-m-d H:i:s", time());
		return new Association($new_handle, $new_secret, $mac_function, $expires_in, $issued, $type, $realm);
	}

	private function __clone()
	{
	}
} 