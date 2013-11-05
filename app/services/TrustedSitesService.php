<?php

namespace services;

use openid\model\IOpenIdUser;
use openid\model\ITrustedSite;
use openid\services\ITrustedSitesService;
use OpenIdTrustedSite;

class TrustedSitesService implements ITrustedSitesService
{

    public function addTrustedSite(IOpenIdUser $user, $realm, $policy, $data = array())
    {
        $old_site = OpenIdTrustedSite::where("realm", "=", $realm)->first();
        $site = new OpenIdTrustedSite;
        $site->realm = $realm;
        $site->policy = $policy;
        $site->user_id = $user->getId();
        $site->data = json_encode($data);
        $site->Save();
    }

    public function delTrustedSite($id)
    {
        $site = OpenIdTrustedSite::where("id", "=", $id)->first();
        if (!is_null($site)) $site->delete();
    }

    /**
     * @param IOpenIdUser $user
     * @param $return_to
     * @return Array | ITrustedSite
     */
    public function getTrustedSites(IOpenIdUser $user, $realm)
    {
        $sites = OpenIdTrustedSite::where("realm", "=", $realm)->where("user_id", "=", $user->getId())->get();
        return $sites;
    }

    public function getAllTrustedSitesByUser(IOpenIdUser $user)
    {
        $sites = OpenIdTrustedSite::where("user_id", "=", $user->getId())->get();
        return $sites;
    }
}