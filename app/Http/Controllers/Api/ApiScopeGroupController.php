<?php namespace App\Http\Controllers\Api;

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

use App\Http\Controllers\ICRUDController;
use Auth\Repositories\IUserRepository;
use OAuth2\Exceptions\InvalidApiScopeGroup;
use OAuth2\Repositories\IApiScopeGroupRepository;
use OAuth2\Services\IApiScopeGroupService;
use OAuth2\Services\IApiScopeService;
use Utils\Services\ILogService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Exception;

/**
 * Class ApiScopeGroupController
 * @package App\Http\Controllers
 */
final class ApiScopeGroupController extends AbstractRESTController implements ICRUDController
{

    /**
     * @var IApiScopeGroupRepository
     */
    private $repository;

    /**
     * @var IApiScopeGroupService
     */
    private $service;

    /**
     * @var IUserRepository
     */
    private $user_repository;

    /**
     * @var IApiScopeService
     */
    private $scope_service;

    /**
     * ApiScopeGroupController constructor.
     * @param IApiScopeGroupService $service
     * @param IApiScopeGroupRepository $repository
     * @param IUserRepository $user_repository
     * @param IApiScopeService $scope_service
     * @param ILogService $log_service
     */
    public function __construct
    (
        IApiScopeGroupService $service,
        IApiScopeGroupRepository $repository,
        IUserRepository $user_repository,
        IApiScopeService $scope_service,
        ILogService $log_service
    )
    {
        parent::__construct($log_service);

        $this->repository                = $repository;
        $this->user_repository           = $user_repository;
        $this->scope_service             = $scope_service;
        $this->service                   = $service;
        $this->allowed_filter_fields     = array('');
        $this->allowed_projection_fields = array('*');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        // TODO: Implement get() method.
    }

    /**
     * @return mixed
     */
    public function create()
    {
        try
        {
            $values = Input::all();

            $rules = array
            (
                'name'   => 'required|text|max:512',
                'active' => 'required|boolean',
                'scopes' => 'required',
                'users'  => 'required|user_ids',
            );
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error400(array('error' => 'validation', 'messages' => $messages));
            }

            $new_group = $this->service->register
            (
                $values['name'],
                $values['active'],
                $values['scopes'],
                $values['users']
            );

            return $this->created(array('group_id' => $new_group->id));
        } catch (InvalidApiScopeGroup $ex1) {
            $this->log_service->error($ex1);

            return $this->error400(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function getByPage()
    {
        try
        {
            $fields    = $this->getProjection(Input::get('fields', null));
            $filters   = $this->getFilters(Input::except('fields', 'limit', 'offset'));
            $page_nbr  = intval(Input::get('offset', 1));
            $page_size = intval(Input::get('limit', 10));

            $list = $this->repository->getAll($page_nbr, $page_size, $filters, $fields);
            $items = array();

            foreach ($list->items() as $g)
            {
                array_push($items, $g->toArray());
            }

            return $this->ok(
                array
                (
                    'page'        => $items,
                    'total_items' => $list->total()
                )
            );
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        try {
            $group = $this->repository->get(intval($id));
            if(is_null($group)) return $this->error404();
            foreach($group->users()->get() as $user)
            {
                foreach($user->clients()->get() as $client)
                {
                    foreach($group->scopes()->get() as $scope)
                        $client->scopes()->detach(intval($scope->id));
                }
            }
            $this->repository->delete($group);
            return $this->deleted();
        }
        catch (Exception $ex)
        {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function update()
    {
        try {

            $values = Input::all();

            $rules = [
               'id'     => 'required|integer',
                'name'   => 'required|text|max:512',
                'active' => 'required|boolean',
                'scopes' => 'required',
                'users'  => 'required|user_ids',
            ];
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);
            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error400(['error' => 'validation', 'messages' => $messages]);
            }

            $this->service->update(intval($values['id']), $values);

            return $this->ok();
        }
        catch (InvalidApiScopeGroup $ex1)
        {
            $this->log_service->error($ex1);
            return $this->error404(array('error' => $ex1->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function activate($id){
        try
        {
            $this->service->setStatus($id, true);
            return $this->ok();
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function deactivate($id){
        try
        {
            $this->service->setStatus($id, false);
            return $this->ok();
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

}