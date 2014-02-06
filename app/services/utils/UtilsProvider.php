<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 2/7/14
 * Time: 4:42 PM
 */

namespace services;

use Illuminate\Support\ServiceProvider;
use utils\services\UtilsServiceCatalog;
use utils\services\Registry;

class UtilsProvider  extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UtilsServiceCatalog::CacheService, 'services\\RedisCacheService');

        $this->app['serverconfigurationservice'] = $this->app->share(function ($app) {
            return new ServerConfigurationService($this->app->make(UtilsServiceCatalog::CacheService));
        });

        $this->app->singleton(UtilsServiceCatalog::LogService, 'services\\LogService');
        $this->app->singleton(UtilsServiceCatalog::LockManagerService, 'services\\LockManagerService');
        $this->app->singleton(UtilsServiceCatalog::ServerConfigurationService, 'services\\ServerConfigurationService');
        $this->app->singleton(UtilsServiceCatalog::BannedIpService, 'services\\utils\\BannedIPService');

        Registry::getInstance()->set(UtilsServiceCatalog::LogService, $this->app->make(UtilsServiceCatalog::LogService));
        Registry::getInstance()->set(UtilsServiceCatalog::ServerConfigurationService, $this->app->make(UtilsServiceCatalog::ServerConfigurationService));
        Registry::getInstance()->set(UtilsServiceCatalog::CacheService, $this->app->make(UtilsServiceCatalog::CacheService));

    }

    public function provides()
    {
        return array('utils.services');
    }

}

