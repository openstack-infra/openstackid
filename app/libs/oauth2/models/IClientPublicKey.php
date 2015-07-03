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

namespace oauth2\models;

/**
 * Interface IClientPublicKey
 * @package oauth2\models
 */
interface IClientPublicKey {
    /**
     * @return int
     */
    public function getId();

    /**
     * @return IClient
     */
    public function getOwner();

    /**
     * @return string
     */
    public function getPEM();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getUse();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return \DateTime
     */
    public function getLastUse();

    /**
     * @return string
     */
    public function getKeyId();

    /**
     * @return string
     */
    public function getSHA_1_Thumbprint();

    /**
     * @return string
     */
    public function getSHA_256_Thumbprint();

    /**
     * @param $kid
     * @param $type
     * @param $use
     * @param $pem
     * @return IClientPublicKey
     */
    static public function buildFromPEM($kid, $type, $use, $pem);

}