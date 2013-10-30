<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 5:16 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\services;


interface IServerConfigurationService {
    public function getOPEndpointURL();
    public function getUserIdentityEndpointURL($identifier);
    public function getPrivateAssociationLifetime();
    public function getSessionAssociationLifetime();
    public function getMaxFailedLoginAttempts();
    public function getNonceLifetime();
    public function isValidIP($remote_address);
}