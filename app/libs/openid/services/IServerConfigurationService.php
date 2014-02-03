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
     * @param $identifier
     * @return mixed
     */
    public function getUserIdentityEndpointURL($identifier);

}