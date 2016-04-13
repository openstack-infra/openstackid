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
/**
 * Class RepositoriesProvider
 * @package Repositories
 */
class RepositoriesProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
    }

    public function register()
    {
        App::singleton(\OpenId\Repositories\IOpenIdAssociationRepository::class, \Repositories\EloquentOpenIdAssociationRepository::class);
        App::singleton(\OpenId\Repositories\IOpenIdTrustedSiteRepository::class, \Repositories\EloquentOpenIdTrustedSiteRepository::class);
        App::singleton(\Auth\Repositories\IUserRepository::class, \Repositories\EloquentUserRepository::class);
        App::singleton(\Auth\Repositories\IMemberRepository::class, \Repositories\EloquentMemberRepository::class);
        App::singleton(\OAuth2\Repositories\IClientPublicKeyRepository::class, \Repositories\EloquentClientPublicKeyRepository::class);
        App::singleton(\OAuth2\Repositories\IServerPrivateKeyRepository::class, \Repositories\EloquentServerPrivateKeyRepository::class);
        App::singleton(\OAuth2\Repositories\IClientRepository::class, \Repositories\EloquentClientRepository::class);
        App::singleton(\OAuth2\Repositories\IApiScopeGroupRepository::class, \Repositories\EloquentApiScopeGroupRepository::class);
        App::singleton(\OAuth2\Repositories\IApiEndpointRepository::class, \Repositories\EloquentApiEndpointRepository::class);
    }
}