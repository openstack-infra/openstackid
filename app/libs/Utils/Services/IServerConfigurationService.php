<?php namespace Utils\Services;

/**
 * Interface IServerConfigurationService
 * @package Utils\Services
 */
interface IServerConfigurationService {
    /**
     * get server configuration param
     * @param $key
     * @return mixed
     */
    public function getConfigValue($key);

    /**
     * @return mixed
     */
    public function getAllConfigValues();

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function saveConfigValue($key,$value);

    /**
     * @return string
     */
    public function getSiteUrl();
} 