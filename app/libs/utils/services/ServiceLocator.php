<?php

namespace utils\services;

use App;

/**
 * Class ServiceLocator
 * @package utils\services
 */
final class ServiceLocator {

    private static $instance = null;

    private function __construct(){
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ServiceLocator();
        }

        return self::$instance;
    }

    public function getService($service_id)
    {
        $service =  App::make($service_id);
        return $service;
    }

    private function __clone()
    {
    }
}