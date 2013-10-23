
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
    public $timestamps = false;

    public function getRealm()
    {
        return $this->realm;
    }

    public function getData()
    {
        $res =  $this->data;
        return json_decode($res);
    }

    public function getUser()
    {
        // TODO: Implement getUser() method.
    }

    public function getAuthorizationPolicy()
    {
       return $this->policy;
    }

}