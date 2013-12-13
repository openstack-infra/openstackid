<?php

namespace utils\services;
use utils\exceptions\UnacquiredLockException;

interface ILockManagerService {
    /**
     * @param $name
     * @param int $lifetime
     * @throws UnacquiredLockException
     * @return mixed
     */
    public function acquireLock($name,$lifetime=3600);
    public function releaseLock($name);
}