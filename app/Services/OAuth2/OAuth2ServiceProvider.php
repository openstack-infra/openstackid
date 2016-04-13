<?php namespace Services\OAuth2;
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

use Illuminate\Support\ServiceProvider;
use OAuth2\Services\AccessTokenGenerator;
use OAuth2\Services\AuthorizationCodeGenerator;
use OAuth2\Services\OAuth2ServiceCatalog;
use OAuth2\Services\RefreshTokenGenerator;
use Utils\Services\UtilsServiceCatalog;
use Illuminate\Support\Facades\App;

/**
 * Class OAuth2ServiceProvider
 * @package Services\OAuth2
 */
final class OAuth2ServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
    }

    public function register()
    {
        App::singleton(\OAuth2\IResourceServerContext::class, \Services\OAuth2\ResourceServerContext::class);
        App::singleton(OAuth2ServiceCatalog::ClientCredentialGenerator, \Services\OAuth2\ClientCredentialGenerator::class);
        App::singleton(OAuth2ServiceCatalog::ClientService, \Services\OAuth2\ClientService::class);
        App::singleton(OAuth2ServiceCatalog::ClientPublicKeyService, \Services\OAuth2\ClientPublicKeyService::class);
        App::singleton(OAuth2ServiceCatalog::ServerPrivateKeyService, \Services\OAuth2\ServerPrivateKeyService::class);
        App::singleton(OAuth2ServiceCatalog::ScopeService, \Services\OAuth2\ApiScopeService::class);
        App::singleton(OAuth2ServiceCatalog::ResourceServerService, \Services\OAuth2\ResourceServerService::class);
        App::singleton(OAuth2ServiceCatalog::ApiService, \Services\OAuth2\ApiService::class);
        App::singleton(OAuth2ServiceCatalog::ApiEndpointService, \Services\OAuth2\ApiEndpointService::class);
        App::singleton(OAuth2ServiceCatalog::UserConsentService, \Services\OAuth2\UserConsentService::class);
        App::singleton(OAuth2ServiceCatalog::OpenIDProviderConfigurationService, \Services\OAuth2\OpenIDProviderConfigurationService::class);
        App::singleton(OAuth2ServiceCatalog::MementoSerializerService, \Services\OAuth2\OAuth2MementoSessionSerializerService::class);
        App::singleton(OAuth2ServiceCatalog::SecurityContextService, \Services\OAuth2\SecurityContextService::class);
        App::singleton(OAuth2ServiceCatalog::PrincipalService, \Services\OAuth2\PrincipalService::class);
        App::singleton(\OAuth2\Services\IClientJWKSetReader::class, \Services\OAuth2\HttpIClientJWKSetReader::class);
        App::singleton(\OAuth2\Services\IApiScopeGroupService::class, \Services\OAuth2\ApiScopeGroupService::class);

        App::singleton(\OAuth2\Builders\IdTokenBuilder::class,function() {
            return new IdTokenBuilderImpl
            (
                App::make(\OAuth2\Repositories\IServerPrivateKeyRepository::class),
                new HttpIClientJWKSetReader
            );
        });

        App::singleton(OAuth2ServiceCatalog::TokenService, function()
        {
            return new TokenService
            (
                App::make(OAuth2ServiceCatalog::ClientService),
                App::make(UtilsServiceCatalog::LockManagerService),
                App::make(UtilsServiceCatalog::ServerConfigurationService),
                App::make(UtilsServiceCatalog::CacheService),
                App::make(UtilsServiceCatalog::AuthenticationService),
                App::make(OAuth2ServiceCatalog::UserConsentService),
                new AuthorizationCodeGenerator(App::make(UtilsServiceCatalog::CacheService)),
                new AccessTokenGenerator(App::make(UtilsServiceCatalog::CacheService)),
                new RefreshTokenGenerator(App::make(UtilsServiceCatalog::CacheService)),
                App::make(\OAuth2\Repositories\IServerPrivateKeyRepository::class),
                new HttpIClientJWKSetReader,
                App::make(OAuth2ServiceCatalog::SecurityContextService),
                App::make(OAuth2ServiceCatalog::PrincipalService),
                App::make(\OAuth2\Builders\IdTokenBuilder::class),
                App::make(\OAuth2\Repositories\IClientRepository::class),
                App::make(\OAuth2\Repositories\IAccessTokenRepository::class),
                App::make(\OAuth2\Repositories\IRefreshTokenRepository::class),
                App::make(\OAuth2\Repositories\IResourceServerRepository::class),
                App::make(UtilsServiceCatalog::TransactionService)
            );
        });

        //OAUTH2 resource server endpoints
        App::singleton(\OAuth2\ResourceServer\IUserService::class, \Services\OAuth2\ResourceServer\UserService::class);
    }

    public function provides()
    {
        return [
            \OAuth2\IResourceServerContext::class,
            OAuth2ServiceCatalog::ClientCredentialGenerator,
            OAuth2ServiceCatalog::ClientService,
            OAuth2ServiceCatalog::ClientPublicKeyService,
            OAuth2ServiceCatalog::ServerPrivateKeyService,
            OAuth2ServiceCatalog::ScopeService,
            OAuth2ServiceCatalog::ResourceServerService,
            OAuth2ServiceCatalog::ApiService,
            OAuth2ServiceCatalog::ApiEndpointService,
            OAuth2ServiceCatalog::UserConsentService,
            OAuth2ServiceCatalog::OpenIDProviderConfigurationService,
            OAuth2ServiceCatalog::MementoSerializerService,
            OAuth2ServiceCatalog::SecurityContextService,
            OAuth2ServiceCatalog::PrincipalService,
            \OAuth2\Services\IClientJWKSetReader::class,
            \OAuth2\Services\IApiScopeGroupService::class,
            \OAuth2\Builders\IdTokenBuilder::class,
            OAuth2ServiceCatalog::TokenService,
            \OAuth2\ResourceServer\IUserService::class
        ];
    }
}