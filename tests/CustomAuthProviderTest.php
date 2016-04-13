<?php
/**
* Copyright 2015 OpenStack Foundation
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

use Auth\CustomAuthProvider;
use Utils\Services\UtilsServiceCatalog;
use OpenId\Services\OpenIdServiceCatalog;
use Auth\Repositories\IUserRepository;
use Auth\Repositories\IMemberRepository;
use Auth\IAuthenticationExtensionService;

/**
 * Class CustomAuthProviderTest
 */
final class CustomAuthProviderTest extends TestCase {

    /**
     * @return CustomAuthProvider
     */
    public function testCreateProvider(){

        $provider =  new CustomAuthProvider(
            App::make(IUserRepository::class),
            App::make(IMemberRepository::class),
            App::make(IAuthenticationExtensionService::class),
            App::make(OpenIdServiceCatalog::UserService),
            App::make(UtilsServiceCatalog::CheckPointService),
            App::make(UtilsServiceCatalog::TransactionService),
            App::make(UtilsServiceCatalog::LogService)
        );

        $this->assertTrue(!is_null($provider));

        return $provider;
    }
}