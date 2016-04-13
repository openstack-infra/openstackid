<?php namespace App\Providers;
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
use Auth;
use App;
use Illuminate\Support\ServiceProvider;
use Auth\CustomAuthProvider as AuthProvider;
use OpenId\Services\OpenIdServiceCatalog;
use Utils\Services\UtilsServiceCatalog;
/**
 * Class CustomAuthProvider
 * @package App\Providers
 */
class CustomAuthProvider extends ServiceProvider {
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Auth::provider('custom', function($app, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\UserProvider...
            return new AuthProvider(
                App::make(\Auth\Repositories\IUserRepository::class),
                App::make(\Auth\Repositories\IMemberRepository::class),
                App::make(\Auth\IAuthenticationExtensionService::class),
                App::make(OpenIdServiceCatalog::UserService),
                App::make(UtilsServiceCatalog::CheckPointService),
                App::make(UtilsServiceCatalog::TransactionService),
                App::make(UtilsServiceCatalog::LogService)
            );
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}