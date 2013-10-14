<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 4:16 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\repositories;


interface IServerConfigurationRepository {
    public function getOPEndpointURL();
}