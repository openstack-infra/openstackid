<?php


namespace openid\services;


interface IServerConfigurationService
{
    /**
     *
     * @return mixed
     */
    public function getOPEndpointURL();

    /**
     *
     * @return mixed
     */
    public function getUserIdentityEndpointURL($identifier);

    public function getConfigValue($key);

}