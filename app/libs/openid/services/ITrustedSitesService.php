<?php


namespace openid\services;

use openid\model\IOpenIdUser;
use openid\model\ITrustedSite;

interface ITrustedSitesService
{
    public function addTrustedSite(IOpenIdUser $user, $realm, $policy, $data = array());

    public function delTrustedSite($id);

    /**
     * @param IOpenIdUser $user
     * @param $return_to
     * @return  \array
     */
    public function getTrustedSites(IOpenIdUser $user, $realm);

    public function getAllTrustedSitesByUser(IOpenIdUser $user);
}