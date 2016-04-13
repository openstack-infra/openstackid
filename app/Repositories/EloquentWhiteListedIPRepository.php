<?php namespace Repositories;
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

use Models\IWhiteListedIPRepository;
use Models\WhiteListedIP;

/**
 * Class EloquentWhiteListedIPRepository
 * @package Repositories
 */
final class EloquentWhiteListedIPRepository
    extends AbstractEloquentEntityRepository
    implements IWhiteListedIPRepository
{

    public function __construct(WhiteListedIP $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param string $ip
     * @return WhiteListedIP
     */
    function getByIp($ip)
    {
        return $this->entity->where('ip','=', $ip)->first();
    }
}