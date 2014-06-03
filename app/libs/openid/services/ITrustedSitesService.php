<?php

namespace openid\services;

use openid\model\IOpenIdUser;
use Exception;
use openid\model\ITrustedSite;

/**
 * Interface ITrustedSitesService
 * @package openid\services
 */
interface ITrustedSitesService {
	/**
	 * @param IOpenIdUser $user
	 * @param             $realm
	 * @param             $policy
	 * @param array       $data
	 * @return bool|ITrustedSite
	 * @throws Exception
	 */
    public function addTrustedSite(IOpenIdUser $user, $realm, $policy, $data = array());

	/**
	 * @param $id
	 * @return bool
	 */
	public function delTrustedSite($id);

    /**
     * @param IOpenIdUser $user
     * @param $realm
     * @param array $data
     * @return mixed
     */
    public function getTrustedSites(IOpenIdUser $user, $realm, $data = array());

}