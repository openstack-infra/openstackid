<?php

use OpenId\Services\OpenIdServiceCatalog;
use OpenId\Helpers\AssociationFactory;
use OpenId\OpenIdProtocol;
use Utils\Services\UtilsServiceCatalog;
use Utils\Exceptions\UnacquiredLockException;

class AssociationServiceTest extends TestCase
{

    public function __construct()
    {

    }

    public function tearDown()
    {
        Mockery::close();
    }

    protected function prepareForTests()
    {
        parent::prepareForTests();
    }

    public function testAddPrivateAssociation()
    {

        $cache_stub = new CacheServiceStub;
        $this->app->instance(UtilsServiceCatalog::CacheService, $cache_stub);

        $lock_manager_service_mock = Mockery::mock(\Utils\Services\ILockManagerService::class);
        $lock_manager_service_mock->shouldReceive('acquireLock')->once();

        $this->app->instance(UtilsServiceCatalog::LockManagerService, $lock_manager_service_mock);

        $service = $this->app[OpenIdServiceCatalog::AssociationService];
        $assoc = AssociationFactory::getInstance()->buildPrivateAssociation('https://www.test.com/', 3600);
        $res = $service->addAssociation($assoc);

        $this->assertTrue(!is_null($res));
    }


    public function testAddSessionAssociation()
    {

        $cache_stub = new CacheServiceStub;
        $this->app->instance(UtilsServiceCatalog::CacheService, $cache_stub);

        $lock_manager_service_mock = Mockery::mock(\Utils\Services\ILockManagerService::class);
        $lock_manager_service_mock->shouldReceive('acquireLock')->once();

        $this->app->instance(UtilsServiceCatalog::LockManagerService, $lock_manager_service_mock);

        $service = $this->app[OpenIdServiceCatalog::AssociationService];
        $assoc = AssociationFactory::getInstance()->buildSessionAssociation(OpenIdProtocol::AssociationSessionTypeDHSHA256, 3600);
        $res = $service->addAssociation($assoc);

        $this->assertTrue(!is_null($res));
    }

    public function testGetSessionAssociationRedisCrash()
    {

        $cache_mock = Mockery::mock(\Utils\Services\ICacheService::class);
        $cache_mock->shouldReceive('storeHash')->once();
        $this->app->instance(UtilsServiceCatalog::CacheService, $cache_mock);

        $lock_manager_service_mock = Mockery::mock(\Utils\Services\ILockManagerService::class);
        $lock_manager_service_mock->shouldReceive('acquireLock')->once();

        $this->app->instance(UtilsServiceCatalog::LockManagerService, $lock_manager_service_mock);

        $service = $this->app[OpenIdServiceCatalog::AssociationService];
        $assoc = AssociationFactory::getInstance()->buildSessionAssociation(OpenIdProtocol::AssociationSessionTypeDHSHA256, 3600);
        $res = $service->addAssociation($assoc);

        $this->assertTrue(!is_null($res));
        $hash = null;
        $cache_mock->shouldReceive('storeHash')->once()->andReturnUsing(function ($name, $values, $ttl) use (&$hash) {
            $hash = $values;
        });
        $cache_mock->shouldReceive('exists')->once()->andReturn(false);
        $cache_mock->shouldReceive('getHash')->once()->andReturnUsing(function ($name, $values) use (&$hash) {
            return $hash;
        });

        $res2 = $service->getAssociation($res->getHandle());

        $this->assertTrue(!is_null($res2));

        $this->assertTrue($res2->getSecret() === $res->getSecret());
    }


    /**
     * @expectedException \OpenId\Exceptions\InvalidAssociation
     */
    public function testGetSessionAssociationMustFail_InvalidAssociation()
    {

        $cache_mock = Mockery::mock(\Utils\Services\ICacheService::class);
        $cache_mock->shouldReceive('storeHash')->once();
        $this->app->instance(UtilsServiceCatalog::CacheService, $cache_mock);

        $lock_manager_service_mock = Mockery::mock(\Utils\Services\ILockManagerService::class);
        $lock_manager_service_mock->shouldReceive('acquireLock')->once();

        $this->app->instance(UtilsServiceCatalog::LockManagerService, $lock_manager_service_mock);

        $repo_mock = Mockery::mock(\OpenId\Repositories\IOpenIdAssociationRepository::class);
        $this->app->instance(\OpenId\Repositories\IOpenIdAssociationRepository::class, $repo_mock);
        $repo_mock->shouldReceive('add')->once();
        $repo_mock->shouldReceive('getByHandle')->once()->andReturnNull();

        $service = $this->app[OpenIdServiceCatalog::AssociationService];
        $assoc = AssociationFactory::getInstance()->buildSessionAssociation(OpenIdProtocol::AssociationSessionTypeDHSHA256, 3600);
        $res = $service->addAssociation($assoc);

        $this->assertTrue(!is_null($res));
        $hash = null;
        $cache_mock->shouldReceive('exists')->once()->andReturn(false);
        $service->getAssociation($res->getHandle());
    }


    /**
     * @expectedException \openid\exceptions\ReplayAttackException
     */
    public function testAddPrivateAssociationMustFail_ReplayAttackException()
    {

        $cache_stub = new CacheServiceStub;
        $this->app->instance(UtilsServiceCatalog::CacheService, $cache_stub);

        $lock_manager_service_mock = Mockery::mock(\Utils\Services\ILockManagerService::class);
        $lock_manager_service_mock->shouldReceive('acquireLock')->once();

        $this->app->instance(UtilsServiceCatalog::LockManagerService, $lock_manager_service_mock);

        $service = $this->app[OpenIdServiceCatalog::AssociationService];
        $assoc = AssociationFactory::getInstance()->buildPrivateAssociation('https://www.test.com/', 3600);
        $res = $service->addAssociation($assoc);

        $this->assertTrue(!is_null($res));
        $lock_manager_service_mock->shouldReceive('acquireLock')->once()->andThrow(new UnacquiredLockException);
        $service->addAssociation($assoc);
    }


    public function testGetPrivateAssociation()
    {

        $cache_stub = new CacheServiceStub;
        $this->app->instance(UtilsServiceCatalog::CacheService, $cache_stub);

        $lock_manager_service_mock = Mockery::mock(\Utils\Services\ILockManagerService::class);
        $lock_manager_service_mock->shouldReceive('acquireLock')->twice();

        $this->app->instance(UtilsServiceCatalog::LockManagerService, $lock_manager_service_mock);

        $service = $this->app[OpenIdServiceCatalog::AssociationService];
        $assoc = AssociationFactory::getInstance()->buildPrivateAssociation('https://www.test.com/', 3600);
        $res = $service->addAssociation($assoc);

        $this->assertTrue(!is_null($res));

        $res2 = $service->getAssociation($res->getHandle(), 'https://www.test.com/');

        $this->assertTrue(!is_null($res2));

        $this->assertTrue($res2->getSecret() === $res->getSecret());
    }


    /**
     * @expectedException \OpenId\Exceptions\OpenIdInvalidRealmException
     */
    public function testGetPrivateAssociationMustFail_OpenIdInvalidRealmException()
    {

        $cache_stub = new CacheServiceStub;
        $this->app->instance(UtilsServiceCatalog::CacheService, $cache_stub);

        $lock_manager_service_mock = Mockery::mock(\Utils\Services\ILockManagerService::class);
        $lock_manager_service_mock->shouldReceive('acquireLock')->once();

        $this->app->instance(UtilsServiceCatalog::LockManagerService, $lock_manager_service_mock);

        $service = $this->app[OpenIdServiceCatalog::AssociationService];
        $assoc = AssociationFactory::getInstance()->buildPrivateAssociation('https://www.test.com/', 3600);
        $res = $service->addAssociation($assoc);

        $this->assertTrue(!is_null($res));

        $service->getAssociation($res->getHandle(), 'https://www1.test.com/');
    }

    /**
     * @expectedException \OpenId\Exceptions\InvalidAssociation
     */
    public function testGetPrivateAssociationMustFail_InvalidAssociation()
    {

        $cache_stub = new CacheServiceStub;
        $this->app->instance(UtilsServiceCatalog::CacheService, $cache_stub);

        $lock_manager_service_mock = Mockery::mock(\Utils\Services\ILockManagerService::class);
        $lock_manager_service_mock->shouldReceive('acquireLock')->once();

        $this->app->instance(UtilsServiceCatalog::LockManagerService, $lock_manager_service_mock);

        $service = $this->app[OpenIdServiceCatalog::AssociationService];
        $assoc = AssociationFactory::getInstance()->buildPrivateAssociation('https://www.test.com/', 3600);
        $res = $service->addAssociation($assoc);

        $this->assertTrue(!is_null($res));

        $service->getAssociation('123456', 'https://www1.test.com/');
    }


    /**
     * @expectedException \OpenId\Exceptions\ReplayAttackException
     */
    public function testGetPrivateAssociationMustFail_ReplayAttackException()
    {


        $cache_stub = new CacheServiceStub;
        $this->app->instance(UtilsServiceCatalog::CacheService, $cache_stub);

        $lock_manager_service_mock = Mockery::mock(\Utils\Services\ILockManagerService::class);
        $lock_manager_service_mock->shouldReceive('acquireLock')->times(2);

        $this->app->instance(UtilsServiceCatalog::LockManagerService, $lock_manager_service_mock);

        $service = $this->app[OpenIdServiceCatalog::AssociationService];
        $assoc = AssociationFactory::getInstance()->buildPrivateAssociation('https://www.test.com/', 3600);
        $res = $service->addAssociation($assoc);

        $this->assertTrue(!is_null($res));

        $res2 = $service->getAssociation($res->getHandle(), 'https://www.test.com/');

        $this->assertTrue(!is_null($res2));

        $this->assertTrue($res2->getSecret() === $res->getSecret());
        $lock_manager_service_mock->shouldReceive('acquireLock')->once()->andThrow(new UnacquiredLockException);
        $service->getAssociation($res->getHandle(), 'https://www.test.com/');
    }
} 