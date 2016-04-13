<?php namespace Services\Utils;
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

use Utils\Services\ICacheService;
use Utils\Services\ILockManagerService;
use Utils\Exceptions\UnacquiredLockException;
use Closure;
use Exception;

/**
 * Class LockManagerService
 * @package Services\Utils
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
     * @return $this
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
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function releaseLock($name)
    {
        $this->cache_service->delete($name);
        return $this;
    }

    /**
     * @param string $name
     * @param Closure $callback
     * @param int $lifetime
     * @return null
     * @throws UnacquiredLockException
     * @throws Exception
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
        catch(Exception $ex)
        {
            $this->releaseLock($name);
            throw $ex;
        }
        return $result;
    }
}