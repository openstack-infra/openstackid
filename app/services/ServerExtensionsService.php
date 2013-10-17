<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace services;

use openid\services\IServerExtensionsService;

class ServerExtensionsService implements IServerExtensionsService{

    public function getAllActiveExtensions()
    {
        return array();
    }
}