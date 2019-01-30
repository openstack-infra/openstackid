<?php namespace Providers\OAuth2;
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
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;
use OAuth2\Strategies\ClientAuthContextValidatorFactory;

/**
 * Class ClientAuthContextValidatorFactoryProvider
 * @package Providers\OAuth2
 */
final class ClientAuthContextValidatorFactoryProvider extends ServiceProvider
{

    public function boot()
    {
        // wait till app is fully booted so we have access to routes
        $this->app->booted(function () {
            ClientAuthContextValidatorFactory::setTokenEndpointUrl
            (
                URL::action('OAuth2\OAuth2ProviderController@token')
            );

            ClientAuthContextValidatorFactory::setJWKSetReader
            (
                App::make(\OAuth2\Services\IClientJWKSetReader::class)
            );
        });

    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // TODO: Implement register() method.
    }
}