<?php

namespace services;

use utils\services\ICacheService;
use utils\services\ILockManagerService;
use utils\exceptions\UnacquiredLockException;

class LockManagerService implements ILockManagerService {

    private $cache_service;

    public function __construct(ICacheService $cache_service){
        $this->cache_service = $cache_service;
    }

    public function acquireLock($name,$lifetime=3600)
    {
        $success = $this->cache_service->addSingleValue($name,time()+$lifetime+1,time()+$lifetime+1);
        if (!$success) { // only one time we could use this handle
            throw new UnacquiredLockException(sprintf("lock name %s",$name));
        }
    }

    public function releaseLock($name)
    {
        $this->cache_service->delete($name);
    }
}