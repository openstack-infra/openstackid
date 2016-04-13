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
use Utils\Model\Identifier;
use Zend\Crypt\Hash;

/**
 * Class UniqueIdentifierGenerator
 * @package Utils\Services
 */
abstract class UniqueIdentifierGenerator implements IdentifierGenerator
{

    /**
     * @var ICacheService
     */
    private $cache_service;

    /**
     * @param ICacheService $cache_service
     */
    public function __construct(ICacheService $cache_service)
    {
        $this->cache_service = $cache_service;
    }

    /**
     * @param Identifier $identifier
     * @return Identifier
     */
    public function generate(Identifier $identifier){

        do
        {
            $key = sprintf("%s.%s", $identifier->getType(), Hash::compute('sha256', $this->_generate($identifier)->getValue()));
        }
        while(!$this->cache_service->addSingleValue($key, $key));
        return $identifier;
    }

    /**
     * @param Identifier $identifier
     * @return Identifier
     */
    abstract protected function _generate(Identifier $identifier);

}