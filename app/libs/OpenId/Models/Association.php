<?php namespace OpenId\Models;

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
use Utils\Model\BaseModelEloquent;

/**
 * Class Association
 * @package OpenId\Models
 */
class Association extends BaseModelEloquent implements IAssociation
{

    /**
     * @var string
     */
    private $handle;
    /**
     * @var string
     */
    private $secret;
    /**
     * @var string
     */
    private $mac_function;
    /**
     * @var int
     */
    private $lifetime;
    /**
     * @var
     */
    private $issued;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $realm;

    /**
     * Association constructor.
     * @param string $handle
     * @param string $secret
     * @param string $mac_function
     * @param int $lifetime
     * @param $issued
     * @param string $type
     * @param string $realm
     */
    public function __construct($handle, $secret, $mac_function, $lifetime, $issued, $type, $realm)
    {
        $this->handle = $handle;
        $this->secret = $secret;
        $this->mac_function = $mac_function;
        $this->lifetime = $lifetime;
        $this->issued = $issued;
        $this->type = $type;
        $this->realm = $realm;
    }

    public function getMacFunction()
    {
        return $this->mac_function;
    }

    public function setMacFunction($mac_function)
    {
        // TODO: Implement setMacFunction() method.
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setSecret($secret)
    {
        // TODO: Implement setSecret() method.
    }

    public function getLifetime()
    {
        return intval($this->lifetime);
    }

    public function setLifetime($lifetime)
    {
        // TODO: Implement setLifetime() method.
    }

    public function getIssued()
    {
        return $this->issued;
    }

    public function setIssued($issued)
    {
        // TODO: Implement setIssued() method.
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        // TODO: Implement setType() method.
    }

    public function getRealm()
    {
        return $this->realm;
    }

    public function setRealm($realm)
    {
        // TODO: Implement setRealm() method.
    }

    public function IsExpired()
    {
        // TODO: Implement IsExpired() method.
    }

    public function getRemainingLifetime()
    {
        // TODO: Implement getRemainingLifetime() method.
    }

    public function getHandle()
    {
        return $this->handle;
    }

}