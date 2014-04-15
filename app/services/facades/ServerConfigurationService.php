<?php

namespace services\facades;

use Illuminate\Support\Facades\Facade;
/**
 * Class ServerConfigurationService
 * @package services\facades
 */
class ServerConfigurationService extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'serverconfigurationservice';
    }
} 