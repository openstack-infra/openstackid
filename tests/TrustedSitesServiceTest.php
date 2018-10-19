<?php
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
use OpenId\Services\OpenIdServiceCatalog;
use Utils\Services\IAuthService;
use OpenId\Repositories\IOpenIdTrustedSiteRepository;
use OpenId\Models\IOpenIdUser;
use Auth\User;
use Repositories\EloquentOpenIdTrustedSiteRepository;
use Tests\BrowserKitTestCase;
/**
 * Class TrustedSitesServiceTest
 */
final class TrustedSitesServiceTest extends BrowserKitTestCase {

	protected function prepareForTests()
	{
		parent::prepareForTests();
	}

	public function tearDown()
    {
        Mockery::close();
    }

	public function testBehaviorAdd(){

		$repo_mock = Mockery::mock(EloquentOpenIdTrustedSiteRepository::class);

		$repo_mock->shouldReceive('add')->andReturn(true)->once();
	    $this->app->instance(IOpenIdTrustedSiteRepository::class, $repo_mock);

		$mock_user = Mockery::mock(IOpenIdUser::class);
		$mock_user->shouldReceive('getId')->andReturn(1);

		$service = $this->app[OpenIdServiceCatalog::TrustedSitesService];
		$res = $service->addTrustedSite($mock_user,
			                            $realm = 'https://www.test.com',
			                            IAuthService::AuthorizationResponse_AllowForever,
			                            $data = array());

		$this->assertTrue(!is_null($res));
	}

	public function testAdd(){
		$service = $this->app[OpenIdServiceCatalog::TrustedSitesService];
        $user = User::where('identifier','=','sebastian.marcet')->first();
		$res = $service->addTrustedSite($user,
			$realm = 'https://www.test.com',
			IAuthService::AuthorizationResponse_AllowForever,
			$data = array());

		$this->assertTrue(!is_null($res));
	}

	public function testGetTrustedSitesByRealm(){

		$realm = 'https://*.test.com';

		$service = $this->app[OpenIdServiceCatalog::TrustedSitesService];

        $user = User::where('identifier','=','sebastian.marcet')->first();

		$res  = $service->addTrustedSite($user,	$realm, IAuthService::AuthorizationResponse_AllowForever, $data = array('email','profile','address'));

		$this->assertTrue(!is_null($res));

		$sites = $service->getTrustedSites($user,'https://www.dev.test.com', $data = array('email','address'));

		$this->assertTrue(is_array($sites));

		$this->assertTrue(count($sites)>0);

	}
} 