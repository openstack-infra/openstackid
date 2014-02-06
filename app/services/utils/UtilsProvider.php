<?php
namespace services\utils;

use Illuminate\Support\ServiceProvider;
use utils\services\UtilsServiceCatalog;
use App;
use Illuminate\Foundation\AliasLoader;

class UtilsProvider  extends ServiceProvider {

    protected $defer = false;
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UtilsServiceCatalog::CacheService, 'services\\utils\\RedisCacheService');

        App::resolving('redis',function($redis){
            $cache_service = $this->app->make(UtilsServiceCatalog::CacheService);
            $cache_service->boot();
        });

        $this->app['serverconfigurationservice'] = $this->app->share(function ($app) {
            return new ServerConfigurationService($this->app->make(UtilsServiceCatalog::CacheService));
        });

        // Shortcut so developers don't need to add an Alias in app/config/app.php
        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('ServerConfigurationService', 'services\\facades\\ServerConfigurationService');
        });

        $this->app->singleton(UtilsServiceCatalog::LogService, 'services\\utils\\LogService');
        $this->app->singleton(UtilsServiceCatalog::LockManagerService, 'services\\utils\\LockManagerService');
        $this->app->singleton(UtilsServiceCatalog::ServerConfigurationService, 'services\\utils\\ServerConfigurationService');
        $this->app->singleton(UtilsServiceCatalog::BannedIpService, 'services\\utils\\BannedIPService');
    }

    public function provides()
    {
        return array('utils.services');
    }

    public function when(){
        return array('redis');
    }
}