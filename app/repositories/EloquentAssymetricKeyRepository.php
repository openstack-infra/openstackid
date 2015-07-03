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

namespace repositories;

use oauth2\models\IAssymetricKey;
use oauth2\repositories\IAssymetricKeyRepository;
use DB;
use utils\services\ILogService;
/**
 * Class EloquentAssymetricKeyRepository
 * @package repositories
 */
abstract class EloquentAssymetricKeyRepository implements IAssymetricKeyRepository
{
    /**
     * @var IAssymetricKey
     */
    protected $key;

    /**
     * @var ILogService
     */
    protected $log_service;


    /**
     * @param string $kid
     * @return IAssymetricKey
     */
    public function get($kid)
    {
        return $this->key->where('kid','=',$kid)->first();
    }

    /**
     * @param IAssymetricKey $key
     * @return void
     */
    public function add(IAssymetricKey $key)
    {
        $key->save();
    }

    /**
     * @param IAssymetricKey $key
     * @return void
     */
    public function delete(IAssymetricKey $key)
    {
        $key->delete();
    }

    /**
     * @param int $id
     * @return IAssymetricKey
     */
    public function getById($id)
    {
        return $this->key->find($id);
    }

    /**
     * @param string $pem
     * @return IAssymetricKey
     */
    public function getByPEM($pem)
    {
        return $this->key->where('pem_content','=',$pem)->first();
    }

    /**
     * @param string $type
     * @param string $usage
     * @param \DateTime $valid_from
     * @param \DateTime $valid_to
     * @return IAssymetricKey
     */
    public function getByValidityRange($type, $usage, \DateTime $valid_from, \DateTime $valid_to)
    {
        // (StartA <= EndB)  and  (EndA >= StartB)
        return $this->key
            ->where('type','=',$type)
            ->where('usage','=',$usage)
            ->where('valid_from','<=',$valid_to)
            ->where('valid_to','>=',$valid_from)
            ->first();
    }


    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return IAssymetricKey[]
     */
    public function getAll($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'))
    {
        DB::getPaginator()->setCurrentPage($page_nbr);
        return $this->key->Filter($filters)->paginate($page_size, $fields);
    }
}