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
use models\marketplace\repositories\ICompanyServiceRepository;
use oauth2\IResourceServerContext;
use utils\services\ILogService;

/**
 * Class OAuth2CompanyServiceApiController
 */
abstract class OAuth2CompanyServiceApiController extends OAuth2ProtectedController{
    /**
     * @var ICompanyServiceRepository
     */
    protected $repository;

    public function __construct (IResourceServerContext $resource_server_context, ILogService $log_service){
        parent::__construct($resource_server_context,$log_service);

        Validator::extend('status', function($attribute, $value, $parameters)
        {
            return $value == ICompanyServiceRepository::Status_All ||
            $value == ICompanyServiceRepository::Status_non_active ||
            $value == ICompanyServiceRepository::Status_active;
        });

        Validator::extend('order', function($attribute, $value, $parameters)
        {
            return $value == ICompanyServiceRepository::Order_date ||
            $value == ICompanyServiceRepository::Order_name ;
        });

        Validator::extend('order_dir', function($attribute, $value, $parameters)
        {
            return $value == 'desc' ||
            $value == 'asc';
        });
    }


    /**
     * query string params:
     * page: You can specify further pages
     * per_page: custom page size up to 100 ( min 10)
     * status: cloud status ( active , not active, all)
     * order_by: order by field
     * order_dir: order direction
     * @return mixed
     */
    public function getCompanyServices()
    {
        try{
            //default params
            $page      = 1;
            $per_page  = 10;
            $status    = ICompanyServiceRepository::Status_All;
            $order_by  = ICompanyServiceRepository::Order_date;
            $order_dir = 'asc';

            //validation of optional parameters

            $values = Input::all();

            $messages = array(
                'status'    => 'The :attribute field is does not has a valid value (all, active, non_active).',
                'order'     => 'The :attribute field is does not has a valid value (date, name).',
                'order_dir' => 'The :attribute field is does not has a valid value (desc, asc).',
            );

            $rules = array(
                'page'          => 'integer|min:1',
                'per_page'      => 'required_with:page|integer|min:10|max:100',
                'status'        => 'status',
                'order_by'      => 'order',
                'order_dir'     => 'required_with:order_by|order_dir',
            );
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules, $messages);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error412($messages);
            }

            if(Input::has('page')){
                $page     = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            if(Input::has('status')){
                $status = Input::get('status');
            }

            if(Input::has('order_by')){
                $order_by  = Input::get('order_by');
                $order_dir = Input::get('order_dir');
            }

            $data = $this->repository->getAll($page, $per_page, $status, $order_by, $order_dir);
            return $this->ok($data);
        }
        catch(Exception $ex){
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCompanyService($id)
    {
        try{
            $data = $this->repository->getById($id);
            return ($data)? $this->ok($data) : $this->error404();
        }
        catch(Exception $ex){
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }
}