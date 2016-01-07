<?php

namespace utils\services;
use utils\exceptions\UnacquiredLockException;
use Closure;
/**
 * Interface ILockManagerService
 * @package utils\services
 */
interface ILockManagerService {
    /**
     * @param $name
     * @param int $lifetime
     * @throws UnacquiredLockException
     * @return mixed
     */
    public function acquireLock($name,$lifetime = 3600);

    /**
     * @param $name
     * @return mixed
     */
    public function releaseLock($name);

    /**
     * @param $name
     * @param Closure $callback
     * @param int $lifetime
     * @return mixed
     */
    public function lock($name, Closure $callback, $lifetime = 3600);
}