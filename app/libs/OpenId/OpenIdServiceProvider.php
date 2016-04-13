<?php namespace OpenId;
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
use OpenId\Extensions\OpenIdAuthenticationExtension;
use OpenId\Services\OpenIdServiceCatalog;
use Utils\Services\UtilsServiceCatalog;
use Illuminate\Support\Facades\App;
/**
 * Class OpenIdServiceProvider
 * Register dependencies with IOC container for package openid
 * @package OpenId
 */
class OpenIdServiceProvider extends ServiceProvider {

    protected $defer = true;

    public function boot(){
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
	    App::singleton('OpenId\IOpenIdProtocol', 'OpenId\OpenIdProtocol');

        $auth_extension_service = App::make('Auth\\IAuthenticationExtensionService');

        if(!is_null($auth_extension_service)){
            $memento_service              = App::make(OpenIdServiceCatalog::MementoSerializerService);
            $server_configuration_service = App::make(UtilsServiceCatalog::ServerConfigurationService);

            $auth_extension_service->addExtension(
                    new OpenIdAuthenticationExtension(
                        $memento_service,
                        $server_configuration_service
                    )
            );
        }
    }

    public function provides()
    {
        return array('openid');
    }
}