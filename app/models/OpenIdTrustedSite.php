
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 6:11 PM
 * To change this template use File | Settings | File Templates.
 */
use openid\model\ITrustedSite;

class OpenIdTrustedSite extends Eloquent implements  ITrustedSite{

    protected $table = 'openid_trusted_sites';

    public function setRealm($realm)
    {
        // TODO: Implement setRealm() method.
    }

    public function getRealm()
    {
        // TODO: Implement getRealm() method.
    }

    public function setData($data)
    {
        // TODO: Implement setData() method.
    }

    public function getData()
    {
        // TODO: Implement getData() method.
    }

    public function getUser()
    {
        // TODO: Implement getUser() method.
    }

    public function getAuthorizationPolicy()
    {
        // TODO: Implement getAuthorizationPolicy() method.
    }

    public function setAuthorizationPolicy($policy)
    {
        // TODO: Implement setAuthorizationPolicy() method.
    }
}