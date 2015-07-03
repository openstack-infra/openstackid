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
interface IClientPublicKey extends IAssymetricKey {

    /**
     * @return IClient
     */
    public function getOwner();

    /**
     * @param string $kid
     * @param string $type
     * @param string $use
     * @param string $pem
     * @param bool $active
     * @param \DateTime $valid_from
     * @param \DateTime $valid_to
     * @return IClientPublicKey
     */
    static public function buildFromPEM($kid, $type, $use, $pem, $active, \DateTime $valid_from, \DateTime $valid_to);

}