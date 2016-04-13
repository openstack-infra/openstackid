<?php namespace Services\Utils;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use Utils\Services\UtilsServiceCatalog;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\App;

class UtilsProvider extends ServiceProvider {

    protected $defer = false;
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        App::singleton(UtilsServiceCatalog::CacheService, 'Services\\Utils\\RedisCacheService');
	    App::singleton(UtilsServiceCatalog::TransactionService, 'Services\\Utils\\EloquentTransactionService');

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
            $loader->alias('ServerConfigurationService', 'Services\\Facades\\ServerConfigurationService');
            $loader->alias('ExternalUrlService', 'Services\\Facades\\ExternalUrlService');
        });

        App::singleton(UtilsServiceCatalog::LogService, 'Services\\Utils\\LogService');
        App::singleton(UtilsServiceCatalog::LockManagerService, 'Services\\Utils\\LockManagerService');
        App::singleton(UtilsServiceCatalog::ServerConfigurationService, 'Services\\Utils\\ServerConfigurationService');
        App::singleton(UtilsServiceCatalog::BannedIpService, 'Services\\Utils\\BannedIPService');
    }

    public function provides()
    {
        return array('utils.services');
    }

    public function when(){
        return array('redis');
    }
}