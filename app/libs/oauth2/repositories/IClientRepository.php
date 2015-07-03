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

namespace oauth2\repositories;

use oauth2\models\IClient;

/**
 * Interface IClientRepository
 * @package oauth2\repositories
 */
interface IClientRepository
{

    /**
     * @param int id
     * @return IClient
     */
    public function get($id);

    /**
     * @param IClient $client
     * @return void
     */
    public function add(IClient $client);

    /**
     * @param IClient $client
     * @return void
     */
    public function delete(IClient $client);
}