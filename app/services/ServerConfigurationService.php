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
        return 360;
    }

    public function getMaxFailedLoginAttempts(){
        return 3;
    }
}