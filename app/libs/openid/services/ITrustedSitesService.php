<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\services;
use openid\model\IOpenIdUser;
use openid\model\ITrustedSite;

interface ITrustedSitesService {
    public function addTrustedSite(IOpenIdUser $user,$realm,$policy,$data=array());
    public function delTrustedSite($id);
    /**
     * @param IOpenIdUser $user
     * @param $return_to
     * @return ITrustedSite
     */
    public function getTrustedSite(IOpenIdUser $user,$realm);

    public function getAllTrustedSitesByUser(IOpenIdUser $user);
}