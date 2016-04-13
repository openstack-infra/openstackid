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

use OAuth2\Repositories\IApiScopeRepository;
use Utils\Services\ILogService;
use OAuth2\Services\IApiScopeService;
use OAuth2\Exceptions\InvalidApi;
use OAuth2\Exceptions\InvalidApiScope;
use App\Http\Controllers\ICRUDController;
use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

/**
 * Class ApiScopeController
 */
class ApiScopeController extends AbstractRESTController implements ICRUDController {

    /**
     * @var IApiScopeService
     */
    private $api_scope_service;

    /**
     * @var IApiScopeRepository
     */
    private $scope_repository;

    public function __construct
    (
        IApiScopeRepository $scope_repository,
        IApiScopeService $api_scope_service,
        ILogService $log_service
    )
    {
        parent::__construct($log_service);
        $this->scope_repository  = $scope_repository;
        $this->api_scope_service = $api_scope_service;
        //set filters allowed values
        $this->allowed_filter_fields     = array('api_id');
        $this->allowed_projection_fields = array('*');
    }

    public function get($id)
    {
        try {
            $scope     = $this->scope_repository->get($id);
            if(is_null($scope)){
                return $this->error404(array('error' => 'scope not found'));
            }
            $data = $scope->toArray();
            return $this->ok($data);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function getByPage()
    {
        try {
            //check for optional filters param on querystring
            $fields    =  $this->getProjection(Input::get('fields',null));
            $filters   = $this->getFilters(Input::except('fields','limit','offset'));
            $page_nbr  = intval(Input::get('offset',1));
            $page_size = intval(Input::get('limit',10));

            $list  = $this->scope_repository->getAll($page_nbr, $page_size, $filters,$fields);
            $items = array();

            foreach ($list->items() as $scope)
            {
                array_push($items, $scope->toArray());
            }

            return $this->ok
            (
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

    public function create()
    {
        try {

            $values = Input::all();

            $rules = array(
                'name'               => 'required|scopename|max:512',
                'short_description'  => 'required|freetext|max:512',
                'description'        => 'required|freetext',
                'active'             => 'required|boolean',
                'default'            => 'required|boolean',
                'system'             => 'required|boolean',
                'api_id'             => 'required|integer',
                'assigned_by_groups' => 'required|boolean',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error'=>'validation','messages' => $messages));
            }

            $new_scope = $this->api_scope_service->add(
                $values['name'],
                $values['short_description'],
                $values['description'],
                $values['active'],
                $values['default'],
                $values['system'],
                $values['api_id'],
                $values['assigned_by_groups']
            );

            return $this->created(array('scope_id' => $new_scope->id));
        }
        catch(InvalidApi $ex1){
            $this->log_service->error($ex1);
            return $this->error404(array('error' => $ex1->getMessage()));
        }
        catch(InvalidApiScope $ex2){
            $this->log_service->error($ex2);
            return $this->error400(array('error' => $ex2->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function delete($id)
    {
        try {
            $res = $this->api_scope_service->delete($id);
            return $res?$this->deleted():$this->error404(array('error'=>'operation failed'));
        }
        catch(InvalidApiScope $ex1){
            $this->log_service->error($ex1);
            return $this->error404(array('error'=>$ex1->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function update()
    {
        try {

            $values = Input::all();

            $rules = array(
                'id'                 => 'required|integer',
                'name'               => 'sometimes|required|scopename|max:512',
                'description'        => 'sometimes|required|freetext',
                'short_description'  => 'sometimes|required|freetext|max:512',
                'active'             => 'sometimes|required|boolean',
                'system'             => 'sometimes|required|boolean',
                'default'            => 'sometimes|required|boolean',
                'assigned_by_groups' => 'sometimes|boolean',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error'=>'validation','messages' => $messages));
            }

            $res = $this->api_scope_service->update(intval($values['id']),$values);

            return $res?$this->ok():$this->error400(array('error'=>'operation failed'));

        }
        catch(InvalidApiScope $ex1){
            $this->log_service->error($ex1);
            return $this->error404(array('error'=>$ex1->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }


    public function activate($id){
        try {
            $res    = $this->api_scope_service->setStatus($id,true);
            return $res?$this->ok():$this->error400(array('error'=>'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

	public function deactivate($id){
		try {
			$res    = $this->api_scope_service->setStatus($id,false);
			return $res?$this->ok():$this->error400(array('error'=>'operation failed'));
		} catch (Exception $ex) {
			$this->log_service->error($ex);
			return $this->error500($ex);
		}
	}

}