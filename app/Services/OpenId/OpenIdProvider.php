<?php namespace Services\OpenId;
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

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use OpenId\Services\NonceUniqueIdentifierGenerator;
use OpenId\Services\OpenIdServiceCatalog;
use Utils\Services\UtilsServiceCatalog;

/**
 * Class OpenIdProvider
 * @package Services\OpenId
 */
final class OpenIdProvider extends ServiceProvider {

    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //register on boot bc we rely on Illuminate\Redis\ServiceProvider\RedisServiceProvider
        App::singleton(OpenIdServiceCatalog::MementoSerializerService, 'Services\\OpenId\\OpenIdMementoSessionSerializerService');
        App::singleton(OpenIdServiceCatalog::ServerExtensionsService, 'Services\\OpenId\\ServerExtensionsService');
        App::singleton(OpenIdServiceCatalog::AssociationService, 'Services\\OpenId\\AssociationService');
        App::singleton(OpenIdServiceCatalog::TrustedSitesService, 'Services\\OpenId\\TrustedSitesService');
        App::singleton(OpenIdServiceCatalog::ServerConfigurationService, 'Services\\Utils\\ServerConfigurationService');
        App::singleton(OpenIdServiceCatalog::UserService, 'Services\\OpenId\\UserService');

        App::singleton(OpenIdServiceCatalog::NonceService, function(){
            return new NonceService(
                App::make(UtilsServiceCatalog::LockManagerService),
                App::make(UtilsServiceCatalog::CacheService),
                App::make(UtilsServiceCatalog::ServerConfigurationService),
                new NonceUniqueIdentifierGenerator(App::make(UtilsServiceCatalog::CacheService))
            );
        });
    }

    public function provides()
    {
        return [
            OpenIdServiceCatalog::MementoSerializerService,
            OpenIdServiceCatalog::ServerExtensionsService,
            OpenIdServiceCatalog::AssociationService,
            OpenIdServiceCatalog::TrustedSitesService,
            OpenIdServiceCatalog::ServerConfigurationService,
            OpenIdServiceCatalog::UserService,
            OpenIdServiceCatalog::NonceService,
        ];
    }
}