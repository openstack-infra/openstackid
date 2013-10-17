<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 3:45 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\services;


interface IServerExtensionsService {
    public function getAllActiveExtensions();
}