<?php

namespace services\utils;

use utils\services\ICacheService;
use utils\services\ILockManagerService;
use utils\exceptions\UnacquiredLockException;
use Closure;

/**
 * Class LockManagerService
 * @package services\utils
 */
final class LockManagerService implements ILockManagerService {

    /**
     * @var ICacheService
     */
    private $cache_service;

    /**
     * LockManagerService constructor.
     * @param ICacheService $cache_service
     */
    public function __construct(ICacheService $cache_service){
        $this->cache_service = $cache_service;
    }

    /**
     * @param string $name
     * @param int $lifetime
     * @throws UnacquiredLockException
     */
    public function acquireLock($name,$lifetime = 3600)
    {
        $time    = time()+$lifetime+1;
        $success = $this->cache_service->addSingleValue($name, $time, $time);
        if (!$success)
        {
            // only one time we could use this handle
            throw new UnacquiredLockException(sprintf("lock name %s",$name));
        }
    }

    /**
     * @param string $name
     */
    public function releaseLock($name)
    {
        $this->cache_service->delete($name);
    }

    /**
     * @param string $name
     * @param Closure $callback
     * @param int $lifetime
     * @return null
     * @throws UnacquiredLockException
     * @throws \Exception
     */
    public function lock($name, Closure $callback, $lifetime = 3600)
    {
        $result = null;

        try
        {
            $this->acquireLock($name, $lifetime);
            $result = $callback($this);
            $this->releaseLock($name);
        }
        catch(UnacquiredLockException $ex1)
        {
            throw $ex1;
        }
        catch(\Exception $ex)
        {
            $this->releaseLock($name);
            throw $ex;
        }
        return $result;
    }
}