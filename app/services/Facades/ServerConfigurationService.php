<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/25/13
 * Time: 4:00 PM
 */

namespace services\Facades;
use Illuminate\Support\Facades\Facade;

class ServerConfigurationService extends Facade  {
    protected static function getFacadeAccessor() { return 'serverconfigurationservice'; }
} 