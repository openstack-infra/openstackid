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
     * @params string $alg
     * @param \DateTime $valid_from
     * @param \DateTime $valid_to
     * @param int|null $owner_id
     * @return IAssymetricKey
     */
    public function getByValidityRange($type, $usage, $alg, \DateTime $valid_from, \DateTime $valid_to, $owner_id = null)
    {
        // (StartA <= EndB)  and  (EndA >= StartB)
        $query = $this->key
            ->where('type','=',$type)
            ->where('usage','=',$usage)
            ->where('alg','=',$alg)
            ->where('valid_from','<=',$valid_to)
            ->where('valid_to','>=',$valid_from)
            ->where('active','=', true);

         if($owner_id)
         {
             $query = $query->where('oauth2_client_id','=', $owner_id);
         }
         return $query->get();
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

    /**
     * @return IAssymetricKey[]
     */
    public function getActives()
    {
        $now = new \DateTime();
        return $this->key
            ->where('active','=', true)
            ->where('valid_from','<=',$now)
            ->where('valid_to','>=',$now)
            ->get();
    }

    /**
     * @param string $type
     * @param string $usage
     * @param string $alg
     * @param int|null $owner_id
     * @return IAssymetricKey
     */
    public function getActiveByCriteria($type, $usage, $alg, $owner_id = null)
    {
        $now = new \DateTime();
        $query = $this->key
            ->where('active','=', true)
            ->where('valid_from','<=',$now)
            ->where('valid_to','>=',$now)
            ->where('type','=',$type)
            ->where('usage','=',$usage)
            ->where('alg','=',$alg);
        if($owner_id)
        {
            $query = $query->where('oauth2_client_id','=', $owner_id);
        }
        return $query->first();
    }
}