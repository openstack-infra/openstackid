<?php
/**
 * Copyright 2015 Openstack Foundation
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

namespace repositories\marketplace;


use IEntity;
use models\marketplace\repositories\ICompanyServiceRepository;
use utils\services\ILogService;
use DB;

/**
 * Class EloquentCompanyServiceRepository
 * @package repositories\marketplace
 */
abstract class EloquentCompanyServiceRepository implements ICompanyServiceRepository{

    /**
     * @var IEntity
     */
    protected $entity;

    /**
     * @var ILogService
     */
    protected $log_service;

    /**
     * @param int $id
     * @return IEntity
     */
    public function getById($id)
    {
        return $this->entity->find($id);
    }

    /**
     * @param int    $page
     * @param int    $per_page
     * @param string $status
     * @param string $order_by
     * @param string $order_dir
     * @return \IEntity[]
     */
    public function getAll($page = 1, $per_page = 1000, $status = ICompanyServiceRepository::Status_All, $order_by = ICompanyServiceRepository::Order_date, $order_dir = 'asc')
    {

        $fields  = array('*');
        $filters = array();
        switch($status){
            case ICompanyServiceRepository::Status_active:
                array_push($filters,
                    array(
                        'name'=>'Active',
                        'op' => '=',
                        'value'=> true
                    )
                );
                break;
            case ICompanyServiceRepository::Status_non_active:
                array_push($filters,
                    array(
                        'name'=>'Active',
                        'op' => '=',
                        'value'=> false
                    )
                );
                break;
        }

        DB::getPaginator()->setCurrentPage($page);
        $query = $this->entity->Filter($filters);

        switch($order_by){
            case ICompanyServiceRepository::Order_date:
                $query = $query->orderBy('Created', $order_dir);
                break;
            case ICompanyServiceRepository::Order_name:
                $query = $query->orderBy('Name', $order_dir);
                break;
        }

        return $query->paginate($per_page, $fields)->toArray();
    }
}