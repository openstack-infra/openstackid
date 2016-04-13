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

use Models\OAuth2\ResourceServer;
use OAuth2\Models\IResourceServer;
use OAuth2\Repositories\IResourceServerRepository;

/**
 * Class EloquentResourceServerRepository
 * @package Repositories
 */
final class EloquentResourceServerRepository extends AbstractEloquentEntityRepository implements IResourceServerRepository
{

    /**
     * EloquentResourceServerRepository constructor.
     * @param ResourceServer $entity
     */
    public function __construct(ResourceServer $entity){
        $this->entity = $entity;
    }


    /**
     * @param string $host
     * @return IResourceServer
     */
    public function getByHost($host)
    {
        // TODO: Implement getByHost() method.
    }

    /**
     * @param string $ip
     * @return IResourceServer
     */
    public function getByIp($ip)
    {
        return $this->entity->where('ips', 'like', '%' . $ip . '%')->first();
    }

    /**
     * @param string $name
     * @return IResourceServer
     */
    public function getByFriendlyName($name)
    {
        // TODO: Implement getByFriendlyName() method.
    }
}