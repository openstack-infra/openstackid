<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/18/13
 * Time: 12:29 PM
 * To change this template use File | Settings | File Templates.
 */

namespace services;
use openid\model\IOpenIdUser;
use openid\model\ITrustedSite;
use openid\services\ITrustedSitesService;

class TrustedSitesService implements ITrustedSitesService {

    public function addTrustedSite(IOpenIdUser $user, $realm, $policy, $data = array())
    {
        // TODO: Implement addTrustedSite() method.
    }

    public function delTrustedSite($realm)
    {
        // TODO: Implement delTrustedSite() method.
    }

    /**
     * @param IOpenIdUser $user
     * @param $return_to
     * @return ITrustedSite
     */
    public function getTrustedSite(IOpenIdUser $user, $return_to)
    {
        // TODO: Implement getTrustedSite() method.
    }
}