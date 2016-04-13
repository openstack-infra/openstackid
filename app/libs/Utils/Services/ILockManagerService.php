<?php namespace Utils\Services;
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
use Utils\Exceptions\UnacquiredLockException;
use Closure;
/**
 * Interface ILockManagerService
 * @package Utils\Services
 */
interface ILockManagerService {
    /**
     * @param string $name
     * @param int $lifetime
     * @throws UnacquiredLockException
     * @return mixed
     */
    public function acquireLock($name,$lifetime = 3600);

    /**
     * @param  string $name
     * @return mixed
     */
    public function releaseLock($name);

    /**
     * @param string $name
     * @param Closure $callback
     * @param int $lifetime
     * @return mixed
     */
    public function lock($name, Closure $callback, $lifetime = 3600);
}