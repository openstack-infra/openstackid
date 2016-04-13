<?php namespace Repositories;
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
use OAuth2\Models\IAsymmetricKey;
use OAuth2\Repositories\IAsymmetricKeyRepository;
use utils\services\ILogService;
use DateTime;
/**
 * Class EloquentAsymmetricKeyRepository
 * @package Repositories
 */
abstract class EloquentAsymmetricKeyRepository extends AbstractEloquentEntityRepository implements IAsymmetricKeyRepository
{
    /**
     * @var ILogService
     */
    protected $log_service;

    /**
     * @param string $kid
     * @return IAsymmetricKey
     */
    public function get($kid)
    {
        return $this->entity->where('kid','=',$kid)->first();
    }

    /**
     * @param int $id
     * @return IAsymmetricKey
     */
    public function getById($id)
    {
        return $this->entity->find($id);
    }

    /**
     * @param string $pem
     * @return IAsymmetricKey
     */
    public function getByPEM($pem)
    {
        return $this->entity->where('pem_content','=',$pem)->first();
    }

    /**
     * @param string $type
     * @param string $usage
     * @params string $alg
     * @param DateTime $valid_from
     * @param DateTime $valid_to
     * @param int|null $owner_id
     * @return IAsymmetricKey
     */
    public function getByValidityRange($type, $usage, $alg, DateTime $valid_from, DateTime $valid_to, $owner_id = null)
    {
        // (StartA <= EndB)  and  (EndA >= StartB)
        $query = $this->entity
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
     * @return IAsymmetricKey[]
     */
    public function getAll($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'))
    {
        return $this->entity->Filter($filters)->paginate($page_size, $fields, $pageName = 'page', $page_nbr);
    }

    /**
     * @return IAsymmetricKey[]
     */
    public function getActives()
    {
        $now = new DateTime();
        return $this->entity
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
     * @return IAsymmetricKey
     */
    public function getActiveByCriteria($type, $usage, $alg, $owner_id = null)
    {
        $now = new DateTime();
        $query = $this->entity
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