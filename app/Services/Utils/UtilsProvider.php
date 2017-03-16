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
use Illuminate\Support\Facades\Log;

/**
 * Class UtilsProvider
 * @package Services\Utils
 */
final class UtilsProvider extends ServiceProvider {

    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        App::singleton(UtilsServiceCatalog::CacheService, 'Services\\Utils\\RedisCacheService');
	    App::singleton(UtilsServiceCatalog::TransactionService, 'Services\\Utils\\EloquentTransactionService');
        App::singleton(UtilsServiceCatalog::LogService, 'Services\\Utils\\LogService');
        App::singleton(UtilsServiceCatalog::LockManagerService, 'Services\\Utils\\LockManagerService');
        App::singleton(UtilsServiceCatalog::ServerConfigurationService, 'Services\\Utils\\ServerConfigurationService');
        App::singleton(UtilsServiceCatalog::BannedIpService, 'Services\\Utils\\BannedIPService');

        // setting facade
        $this->app['serverconfigurationservice'] = App::share(function ($app) {
            return new ServerConfigurationService
            (
                App::make(UtilsServiceCatalog::CacheService),
                App::make(UtilsServiceCatalog::TransactionService)
            );
        });

        // setting facade
        $this->app['externalurlservice'] = App::share(function ($app) {
            return new ExternalUrlService();
        });

    }

    public function provides()
    {
        return
            [
                UtilsServiceCatalog::CacheService,
                UtilsServiceCatalog::TransactionService,
                UtilsServiceCatalog::LogService,
                UtilsServiceCatalog::LockManagerService,
                UtilsServiceCatalog::ServerConfigurationService,
                UtilsServiceCatalog::BannedIpService,
                \Services\Facades\ServerConfigurationService::class,
                \Services\Facades\ExternalUrlService::class,
                'serverconfigurationservice',
                'externalurlservice',
                'ServerConfigurationService',
                'ExternalUrlService',
            ];
    }

    public function when(){
        return array('redis');
    }
}