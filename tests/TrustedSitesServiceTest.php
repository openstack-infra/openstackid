<?php

use OpenId\Services\OpenIdServiceCatalog;
use Utils\Services\IAuthService;
use OpenId\Repositories\IOpenIdTrustedSiteRepository;
use OpenId\Models\IOpenIdUser;
use Auth\User;
use Repositories\EloquentOpenIdTrustedSiteRepository;
use Way\Tests\Factory;
/**
 * Class TrustedSitesServiceTest
 */
class TrustedSitesServiceTest extends TestCase {

	public function __construct()
    {
	}

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

		$user = Factory::create(User::class);

		$res = $service->addTrustedSite($user,
			$realm = 'https://www.test.com',
			IAuthService::AuthorizationResponse_AllowForever,
			$data = array());

		$this->assertTrue(!is_null($res));

	}



	public function testGetTrustedSitesByRealm(){

		$realm = 'https://*.test.com';

		$service = $this->app[OpenIdServiceCatalog::TrustedSitesService];

		$user = Factory::create(User::class);

		$res  = $service->addTrustedSite($user,	$realm, IAuthService::AuthorizationResponse_AllowForever, $data = array('email','profile','address'));

		$this->assertTrue(!is_null($res));

		$sites = $service->getTrustedSites($user,'https://www.dev.test.com', $data = array('email','address'));

		$this->assertTrue(is_array($sites));

		$this->assertTrue(count($sites)>0);

	}
} 