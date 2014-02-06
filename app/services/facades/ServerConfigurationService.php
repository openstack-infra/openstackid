<?php

namespace services\facades;

use Illuminate\Support\Facades\Facade;

class ServerConfigurationService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'serverconfigurationservice';
    }
} 