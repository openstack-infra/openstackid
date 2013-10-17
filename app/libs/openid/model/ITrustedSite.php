<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 3:58 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\model;


interface ITrustedSite {

    public function setRealm($realm);
    public function getRealm();

    public function setData($data);
    public function getData();

    public function getUser();

    public function getAuthorizationPolicy();
    public function setAuthorizationPolicy($policy);
}