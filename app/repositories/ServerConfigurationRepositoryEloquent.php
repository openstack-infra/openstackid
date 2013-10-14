<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:04 PM
 * To change this template use File | Settings | File Templates.
 */

namespace repositories;
use openid\repositories\IServerConfigurationRepository;

class ServerConfigurationRepositoryEloquent implements  IServerConfigurationRepository {

    public function getOPEndpointURL()
    {
        return "https://dev.openstack.id.com";
    }
}