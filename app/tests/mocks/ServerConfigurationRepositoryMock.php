<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:10 PM
 * To change this template use File | Settings | File Templates.
 */

use openid\repositories\IServerConfigurationRepository;

class ServerConfigurationRepositoryMock implements IServerConfigurationRepository{

    public function getOPEndpointURL()
    {
        return "https://dev.openstack.id.com";
    }
}