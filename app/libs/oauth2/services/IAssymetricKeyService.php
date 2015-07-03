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

namespace oauth2\services;


use oauth2\models\IAssymetricKey;

interface IAssymetricKeyService
{
    /**
     * @param array $params
     * @return IAssymetricKey
     */
    public function register(array $params);

    /**
     * @param $key_id
     * @return bool
     */
    public function delete($key_id);

    /**
     * @param int $key_id
     * @param array $params
     * @return bool
     */
    public function update($key_id, array $params);

}