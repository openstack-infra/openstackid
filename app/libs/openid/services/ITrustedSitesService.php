<?php


namespace openid\services;

use openid\model\IOpenIdUser;

interface ITrustedSitesService
{
    public function addTrustedSite(IOpenIdUser $user, $realm, $policy, $data = array());

    public function delTrustedSite($id);

    /**
     * @param IOpenIdUser $user
     * @param $realm
     * @param array $data
     * @return mixed
     */
    public function getTrustedSites(IOpenIdUser $user, $realm, $data = array());

    public function getAllTrustedSitesByUser(IOpenIdUser $user);
}