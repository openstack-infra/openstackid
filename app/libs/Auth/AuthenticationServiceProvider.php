<?php namespace Auth;
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
use Utils\Services\UtilsServiceCatalog;
/**
 * Class AuthenticationServiceProvider
 * @package auth
 */
final class AuthenticationServiceProvider extends ServiceProvider
{

    protected $defer = true;

    public function boot()
    {
    }

    public function register()
    {
        App::singleton(UtilsServiceCatalog::AuthenticationService, 'Auth\\AuthService');
        App::singleton(\Auth\IAuthenticationExtensionService::class, 'Auth\\AuthenticationExtensionService');
        App::singleton(\Auth\IUserNameGeneratorService::class, 'Auth\\UserNameGeneratorService');
    }

    public function provides()
    {
        return [
            UtilsServiceCatalog::AuthenticationService,
            \Auth\IAuthenticationExtensionService::class,
            \Auth\IUserNameGeneratorService::class,
        ];
    }
}