<?php
use openid\services\OpenIdServiceCatalog;
use utils\services\IAuthService;
use Way\Tests\Factory;

/**
 * Class TrustedSitesServiceTest
 */
class TrustedSitesServiceTest extends TestCase {

	public function __construct(){

	}

    public function tearDown()
    {
        Mockery::close();
    }

	protected function prepareForTests()
	{
		parent::prepareForTests();

	}

	public function testBehaviorAdd(){

		$trusted_site = Mockery::mock('Eloquent','OpenIdTrustedSite');
		$trusted_site->shouldReceive('create')->andReturn($trusted_site)->once();
        $this->app->instance('OpenIdTrustedSite', $trusted_site);

		$mock_user = Mockery::mock('openid\model\IOpenIdUser');
		$mock_user->shouldReceive('getId')->andReturn(1);

		$service = $this->app[OpenIdServiceCatalog::TrustedSitesService];
		$res = $service->addTrustedSite($mock_user,
			                            $realm = 'https://www.test.com',
			                            IAuthService::AuthorizationResponse_AllowForever,
			                            $data = array());

		$this->assertTrue(!is_null($res));
		$this->assertTrue($res===$trusted_site);
	}

	public function testAdd(){

		$service = $this->app[OpenIdServiceCatalog::TrustedSitesService];

		$user = Factory::create('auth\User');

		$res = $service->addTrustedSite($user,
			$realm = 'https://www.test.com',
			IAuthService::AuthorizationResponse_AllowForever,
			$data = array());

		$this->assertTrue(!is_null($res));

	}



	public function testGetTrustedSitesByRealm(){

		$realm = 'https://*.test.com';

		$service = $this->app[OpenIdServiceCatalog::TrustedSitesService];

		$user = Factory::create('auth\User');

		$res  = $service->addTrustedSite($user,	$realm, IAuthService::AuthorizationResponse_AllowForever, $data = array('email','profile','address'));

		$this->assertTrue(!is_null($res));

		$sites = $service->getTrustedSites($user,'https://www.dev.test.com', $data = array('email','address'));

		$this->assertTrue(is_array($sites));

		$this->assertTrue(count($sites)>0);

	}
} 