<?php namespace Repositories;
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
use Models\OAuth2\AccessToken;
use Models\OAuth2\Api;
use Models\OAuth2\ApiEndpoint;
use Models\OAuth2\Client;
use Models\OAuth2\RefreshToken;
use Models\OAuth2\ResourceServer;
use Models\WhiteListedIP;
use Utils\Services\UtilsServiceCatalog;

/**
 * Class RepositoriesProvider
 * @package Repositories
 */
final class RepositoriesProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot(){
    }

    public function register(){

        App::singleton(\OpenId\Repositories\IOpenIdAssociationRepository::class, \Repositories\EloquentOpenIdAssociationRepository::class);
        App::singleton(\OpenId\Repositories\IOpenIdTrustedSiteRepository::class, \Repositories\EloquentOpenIdTrustedSiteRepository::class);
        App::singleton(\Auth\Repositories\IUserRepository::class, \Repositories\EloquentUserRepository::class);
        App::singleton(\Auth\Repositories\IMemberRepository::class, \Repositories\EloquentMemberRepository::class);
        App::singleton(\OAuth2\Repositories\IClientPublicKeyRepository::class, \Repositories\EloquentClientPublicKeyRepository::class);
        App::singleton(\OAuth2\Repositories\IServerPrivateKeyRepository::class, \Repositories\EloquentServerPrivateKeyRepository::class);

        App::singleton(\OAuth2\Repositories\IClientRepository::class, function(){
            return new CacheClientRepository(
                new EloquentClientRepository(new Client(), App::make(UtilsServiceCatalog::LogService))
            );
        });

        App::singleton(\OAuth2\Repositories\IResourceServerRepository::class, function(){
            return new CacheResourceServerRepository(new EloquentResourceServerRepository(new ResourceServer()));
        });

        App::singleton(\Models\IWhiteListedIPRepository::class, function (){
           return new CacheWhiteListedIPRepository(new EloquentWhiteListedIPRepository(new WhiteListedIP()));
        });

        App::singleton(\OAuth2\Repositories\IApiRepository::class, function(){
            return new CacheApiRepository(new EloquentApiRepository(new Api()));
        });

        App::singleton(\OAuth2\Repositories\IApiScopeRepository::class, \Repositories\EloquentApiScopeRepository::class);

        App::singleton(\OAuth2\Repositories\IApiEndpointRepository::class, function(){
            return new CacheApiEndpointRepository(new EloquentApiEndpointRepository(new ApiEndpoint()));
        });

        App::singleton(\OAuth2\Repositories\IApiScopeGroupRepository::class, \Repositories\EloquentApiScopeGroupRepository::class);

        App::singleton(\OAuth2\Repositories\IRefreshTokenRepository::class, function(){
            return new CacheRefreshTokenRepository
            (
                new EloquentRefreshTokenRepository
                (
                    new RefreshToken(),
                    App::make(UtilsServiceCatalog::LogService)
                )
            );
        });

        App::singleton(\OAuth2\Repositories\IAccessTokenRepository::class, function(){
            return new CacheAccessTokenRepository
            (
                new EloquentAccessTokenRepository
                (
                    new AccessToken(),
                    App::make(UtilsServiceCatalog::LogService)
                )
            );
        });

    }

    public function provides()
    {
        return [
            \OpenId\Repositories\IOpenIdAssociationRepository::class,
            \OpenId\Repositories\IOpenIdTrustedSiteRepository::class,
            \Auth\Repositories\IUserRepository::class,
            \Auth\Repositories\IMemberRepository::class,
            \OAuth2\Repositories\IClientPublicKeyRepository::class,
            \OAuth2\Repositories\IServerPrivateKeyRepository::class,
            \OAuth2\Repositories\IClientRepository::class,
            \OAuth2\Repositories\IApiScopeGroupRepository::class,
            \OAuth2\Repositories\IApiEndpointRepository::class,
            \OAuth2\Repositories\IRefreshTokenRepository::class,
            \OAuth2\Repositories\IAccessTokenRepository::class,
            \OAuth2\Repositories\IApiScopeRepository::class,
            \OAuth2\Repositories\IApiRepository::class,
            \OAuth2\Repositories\IResourceServerRepository::class,
            \Models\IWhiteListedIPRepository::class,
        ];
    }
}