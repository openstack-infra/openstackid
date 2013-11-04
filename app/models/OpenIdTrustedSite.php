
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

    public function user(){
        return $this->belongs_to("OpenIdUser");
    }

    public function getRealm()
    {
        return $this->realm;
    }

    public function getData()
    {
        $res =  $this->data;
        return json_decode($res);
    }

    public function getUITrustedData(){
        $data = $this->getData();
        $str = '';
        foreach($data as $val){
            $str .= $val. ', ';
        }
        return trim($str,', ');
    }

    public function getUser()
    {
        return $this->user();
    }

    public function getAuthorizationPolicy()
    {
       return $this->policy;
    }

}