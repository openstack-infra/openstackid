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
use Utils\Model\IEntity;
/**
 * Interface IAssociation
 * @package OpenId\Models
 */
interface IAssociation extends IEntity {

    const TypePrivate = 1;
    const TypeSession = 2;

    /**
     * @return string
     */
    public function getMacFunction();

    /**
     * @param string $mac_function
     * @return $this
     */
    public function setMacFunction($mac_function);

    /**
     * @return string
     */
    public function getSecret();

    /**
     * @param string $secret
     * @return $this
     */
    public function setSecret($secret);

    /**
     * @return int
     */
    public function getLifetime();

    /**
     * @param int $lifetime
     * @return $this
     */
    public function setLifetime($lifetime);

    public function getIssued();

    public function setIssued($issued);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getRealm();

    /**
     * @param string $realm
     * @return $this
     */
    public function setRealm($realm);

    /**
     * @return bool
     */
    public function IsExpired();

    public function getRemainingLifetime();

	public function getHandle();

}