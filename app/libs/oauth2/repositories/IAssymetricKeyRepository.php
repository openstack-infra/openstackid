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

use oauth2\models\IAssymetricKey;

/**
 * Interface IAssymetricKeyRepository
 * @package oauth2\repositories
 */
interface IAssymetricKeyRepository
{
    /**
     * @param string $kid
     * @return IAssymetricKey
     */
    public function get($kid);

    /**
     * @param string $pem
     * @return IAssymetricKey
     */
    public function getByPEM($pem);

    /**
     * @param string $type
     * @param string $usage
     * @params string $alg
     * @param \DateTime $valid_from
     * @param \DateTime $valid_to
     * @return IAssymetricKey
     */
    public function getByValidityRange($type, $usage, $alg, \DateTime $valid_from, \DateTime $valid_to);

    /**
     * @param int $id
     * @return IAssymetricKey
     */
    public function getById($id);

    /**
     * @param IAssymetricKey $key
     * @return void
     */
    public function add(IAssymetricKey $key);

    /**
     * @param IAssymetricKey $key
     * @return void
     */
    public function delete(IAssymetricKey $key);

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return IAssymetricKey[]
     */
    public function getAll($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'));

    /**
     * @return IAssymetricKey[]
     */
    public function getActives();

    /**
     * @param string $type
     * @param string $usage
     * @param string $alg
     * @return IAssymetricKey
     */
    public function getActiveByCriteria($type, $usage, $alg);

}