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
 * Interface IServerPrivateKey
 * @package oauth2\models
 */
interface IServerPrivateKey extends IAssymetricKey
{
    /**
     * @return string
     */
    public function getPassword();

    /**
    * @param string $kid
    * @param \DateTime $valid_from
    * @param \DateTime $valid_to
    * @param string $type
    * @param string $use
    * @param bool $active
    * @param string $pem_content
    * @param null|string $password
    * @return IServerPrivateKey
    */
    static function build($kid, \DateTime $valid_from, \DateTime $valid_to, $type, $use, $active, $pem_content, $password = null);
}