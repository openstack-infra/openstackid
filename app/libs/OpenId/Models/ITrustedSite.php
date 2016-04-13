<?php namespace OpenId\Models;
/**
 * Interface ITrustedSite
 * @package openid\model
 */
interface ITrustedSite {
    /**
     * @return string
     */
    public function getRealm();

    public function getData();

    public function getUser();

    public function getAuthorizationPolicy();

    public function getUITrustedData();
}