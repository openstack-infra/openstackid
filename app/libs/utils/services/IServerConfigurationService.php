<?php

namespace utils\services;


interface IServerConfigurationService {
    /**
     * get server configuration param
     * @param $key
     * @return mixed
     */
    public function getConfigValue($key);

    public function getAllConfigValues();

    public function saveConfigValue($key,$value);
} 