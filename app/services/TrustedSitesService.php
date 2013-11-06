<?php

namespace services;

use openid\model\IOpenIdUser;
use openid\model\ITrustedSite;
use openid\services\ILogService;
use openid\services\ITrustedSitesService;
use OpenIdTrustedSite;

class TrustedSitesService implements ITrustedSitesService
{
    private $log;

    public function __construct(ILogService $log)
    {
        $this->log = $log;
    }

    public function addTrustedSite(IOpenIdUser $user, $realm, $policy, $data = array())
    {
        $res = false;
        try {
            $site = new OpenIdTrustedSite;
            $site->realm = $realm;
            $site->policy = $policy;
            $site->user_id = $user->getId();
            $site->data = json_encode($data);
            $site->Save();
            $res = true;
        } catch (\Exception $ex) {
            $this->log->error($ex);
        }
        return $res;
    }

    public function delTrustedSite($id)
    {
        try {
            $site = OpenIdTrustedSite::where("id", "=", $id)->first();
            if (!is_null($site)) $site->delete();
        } catch (\Exception $ex) {
            $this->log->error($ex);
        }
    }

    /**
     * @param IOpenIdUser $user
     * @param $return_to
     * @return Array | ITrustedSite
     */
    public function getTrustedSites(IOpenIdUser $user, $realm, $data = array())
    {
        $sites = null;
        try {
            if (count($data) > 0) {
                $json_data = json_encode($data);
                $sites = OpenIdTrustedSite::where("realm", "=", $realm)
                    ->where("user_id", "=", $user->getId())
                    ->where("data", "=", $json_data)
                    ->get();
                if (count($sites) > 0)
                    return $sites;
            }
            $sites = OpenIdTrustedSite::where("realm", "=", $realm)->where("user_id", "=", $user->getId())->get();
        } catch (\Exception $ex) {
            $this->log->error($ex);
        }
        return $sites;
    }

    public function getAllTrustedSitesByUser(IOpenIdUser $user)
    {
        $sites = null;
        try {
            $sites = OpenIdTrustedSite::where("user_id", "=", $user->getId())->get();
        } catch (\Exception $ex) {
            $this->log->error($ex);
        }
        return $sites;
    }
}