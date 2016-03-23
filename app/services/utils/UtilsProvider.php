<?php
namespace services\utils;

use Illuminate\Support\ServiceProvider;
use utils\services\UtilsServiceCatalog;
use Illuminate\Foundation\AliasLoader;
use App;

class UtilsProvider extends ServiceProvider {

    protected $defer = false;
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        App::singleton(UtilsServiceCatalog::CacheService, 'services\\utils\\RedisCacheService');
	    App::singleton(UtilsServiceCatalog::TransactionService, 'services\\utils\\EloquentTransactionService');

        App::resolving('redis',function($redis){
            $cache_service = App::make(UtilsServiceCatalog::CacheService);
            $cache_service->boot();
        });

        $this->app['serverconfigurationservice'] = App::share(function ($app) {
            return new ServerConfigurationService(App::make(UtilsServiceCatalog::CacheService),App::make(UtilsServiceCatalog::TransactionService));
        });

        $this->app['externalurlservice'] = App::share(function ($app) {
            return new ExternalUrlService();
        });

        // Shortcut so developers don't need to add an Alias in app/config/app.php
        App::booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('ServerConfigurationService', 'services\\facades\\ServerConfigurationService');
            $loader->alias('ExternalUrlService', 'services\\facades\\ExternalUrlService');
        });

        App::singleton(UtilsServiceCatalog::LogService, 'services\\utils\\LogService');
        App::singleton(UtilsServiceCatalog::LockManagerService, 'services\\utils\\LockManagerService');
        App::singleton(UtilsServiceCatalog::ServerConfigurationService, 'services\\utils\\ServerConfigurationService');
        App::singleton(UtilsServiceCatalog::BannedIpService, 'services\\utils\\BannedIPService');

    }

    public function provides()
    {
        return array('utils.services');
    }

    public function when(){
        return array('redis');
    }
}