<?php
/**
* Copyright 2015 OpenStack Foundation
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

namespace utils\model;

use utils\services\IdentifierGenerator;

/**
 * Class Identifier
 * @package utils\model
 */
abstract class Identifier
{

    /**
     * @param int $len
     * @param int $lifetime
     */
    public function __construct($len, $lifetime = 0 )
    {
        $this->lifetime = $lifetime;
        $this->len      = $len;
    }

    /**
     * @var int
     */
    protected $len;

    /**
     * @var int
     */
    protected $lifetime;
    /**
     * @var string
     */
    protected $value;

    /**
     * @param IdentifierGenerator $generator
     * @return $this
     */
    public function generate(IdentifierGenerator $generator)
    {
        return $generator->generate($this);
    }

    /**
     * @return int
     */
    public function getLenght()
    {
        return $this->len;
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        return intval($this->lifetime);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}