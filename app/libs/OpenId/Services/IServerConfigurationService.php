<?php namespace OpenId\Services;
/**
 * Interface IServerConfigurationService
 * @package OpenId\Services
 */
interface IServerConfigurationService {
    /**
     *
     * @return string
     */
    public function getOPEndpointURL();

    /**
     * @param string $identifier
     * @return string
     */
    public function getUserIdentityEndpointURL($identifier);

}