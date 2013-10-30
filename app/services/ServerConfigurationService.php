<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/18/13
 * Time: 12:30 PM
 * To change this template use File | Settings | File Templates.
 */

namespace services;
use openid\services\IServerConfigurationService;
use \BannedIP;
class ServerConfigurationService implements IServerConfigurationService{

    public function getUserIdentityEndpointURL($identifier){
        $url = action("UserController@getIdentity",array("identifier"=>$identifier));
        return $url;
    }

    public function getOPEndpointURL()
    {
        $url = action("OpenIdProviderController@op_endpoint");
        return $url;
    }

    public function getPrivateAssociationLifetime()
    {
        return 120;
    }

    public function getSessionAssociationLifetime()
    {
        return 3600*6;
    }

    public function getMaxFailedLoginAttempts(){
        return 3;
    }

    public function getNonceLifetime(){
        return 360;
    }


    public function isValidIP($remote_address){
        $res = true;
        $banned_ip = BannedIP::where("ip","=",$remote_address)->first();
        if($banned_ip){
            $banned_ip->hits = $banned_ip->hits + 1;
            $banned_ip->Save();
            sleep(2 ^ $banned_ip->hits);
            $res = false;
        }
        return $res;
    }
}