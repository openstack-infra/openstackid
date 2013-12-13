<?php

namespace services;

use utils\services\ILockManagerService;
use utils\exceptions\UnacquiredLockException;

class LockManagerService implements ILockManagerService {

    private $redis;

    public function __construct(){
        $this->redis = \RedisLV4::connection();
    }

    public function acquireLock($name,$lifetime=3600)
    {
        $success = $this->redis->setnx($name , time()+$lifetime+1);
        if (!$success) { // only one time we could use this handle
            throw new UnacquiredLockException(sprintf("lock name %s",$name));
        }
    }

    public function releaseLock($name)
    {
        $this->redis->del($name);
    }
}