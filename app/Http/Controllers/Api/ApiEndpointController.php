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
use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use OAuth2\Exceptions\InvalidApiEndpoint;
use OAuth2\Exceptions\InvalidApiScope;
use OAuth2\Services\IApiEndpointService;
use Utils\Services\ILogService;

/**
 * Class ApiEndpointController
 * REST Controller for Api endpoint entity CRUD ops
 */
class ApiEndpointController extends AbstractRESTController implements ICRUDController {

    private $api_endpoint_service;

    public function __construct(IApiEndpointService $api_endpoint_service, ILogService $log_service)
    {
        parent::__construct($log_service);
        $this->api_endpoint_service = $api_endpoint_service;
        //set filters allowed values
        $this->allowed_filter_fields     = array('api_id');
        $this->allowed_projection_fields = array('*');
    }

    public function get($id)
    {
        try {
            $api_endpoint = $this->api_endpoint_service->get($id);
            if(is_null($api_endpoint)){
                return $this->error404(array('error' => 'api endpoint not found'));
            }
            $scopes         = $api_endpoint->scopes()->get(array('id','name'));
            $data           = $api_endpoint->toArray();
            $data['scopes'] = $scopes->toArray();
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
            $fields  =  $this->getProjection(Input::get('fields',null));
            $filters = $this->getFilters(Input::except('fields','limit','offset'));
            $page_nbr = intval(Input::get('offset',1));
            $page_size = intval(Input::get('limit',10));
            $list = $this->api_endpoint_service->getAll($page_nbr, $page_size, $filters,$fields);
            $items = array();
            foreach ($list->getItems() as $api_endpoint) {
                array_push($items, $api_endpoint->toArray());
            }
            return $this->ok( array(
                'page' => $items,
                'total_items' => $list->getTotal()
            ));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function create()
    {
        try {
            $new_api_endpoint = Input::all();

            $rules = array(
                'name'               => 'required|alpha_dash|max:255',
                'description'        => 'required|freetext',
                'active'             => 'required|boolean',
                'allow_cors'         => 'required|boolean',
                'route'              => 'required|route',
                'http_method'        => 'required|httpmethod',
                'api_id'             => 'required|integer',
                'rate_limit'         => 'required|integer',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($new_api_endpoint, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error'=>'validation','messages' => $messages));
            }

            $new_api_endpoint_model = $this->api_endpoint_service->add(
                $new_api_endpoint['name'],
                $new_api_endpoint['description'],
                $new_api_endpoint['active'],
                $new_api_endpoint['allow_cors'],
                $new_api_endpoint['route'],
                $new_api_endpoint['http_method'],
                $new_api_endpoint['api_id'],
                $new_api_endpoint['rate_limit']
            );
            return $this->created(array('api_endpoint_id' => $new_api_endpoint_model->id));
        }
        catch (InvalidApiEndpoint $ex1) {
            $this->log_service->error($ex1);
            return $this->error400(array('error'=>$ex1->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function delete($id)
    {
        try {
            $res = $this->api_endpoint_service->delete($id);
            return $res?$this->deleted():$this->error404(array('error'=>'operation failed'));
        }
        catch (InvalidApiEndpoint $ex1) {
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
                'name'               => 'sometimes|required|alpha_dash|max:255',
                'description'        => 'sometimes|required|freetext',
                'active'             => 'sometimes|required|boolean',
                'allow_cors'         => 'sometimes|required|boolean',
                'route'              => 'sometimes|required|route',
                'http_method'        => 'sometimes|required|httpmethod',
                'rate_limit'         => 'sometimes|integer',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error'=>'validation','messages' => $messages));
            }

            $res = $this->api_endpoint_service->update(intval($values['id']),$values);

            return $res?$this->ok():$this->error400(array('error'=>'operation failed'));
        }
        catch(InvalidApiEndpoint $ex1){
            $this->log_service->error($ex1);
            return $this->error400(array('error'=>$ex1->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function activate($id){
        try {
            $res    = $this->api_endpoint_service->setStatus($id,true);
            return $res?$this->ok():$this->error400(array('error'=>'operation failed'));
        }
        catch (InvalidApiEndpoint $ex1) {
            $this->log_service->error($ex1);
            return $this->error404(array('error'=>$ex1->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function deactivate($id){
        try {
            $res    = $this->api_endpoint_service->setStatus($id,false);
            return $res?$this->ok():$this->error400(array('error'=>'operation failed'));
        }
        catch (InvalidApiEndpoint $ex1) {
            $this->log_service->error($ex1);
            return $this->error404(array('error'=>$ex1->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function addRequiredScope($id, $scope_id){
        try {
            $res = $this->api_endpoint_service->addRequiredScope($id,$scope_id);
            return $res?$this->ok():$this->error400(array('error'=>'operation failed'));
        }
        catch (InvalidApiEndpoint $ex1) {
            $this->log_service->error($ex1);
            return $this->error400(array('error'=>$ex1->getMessage()));
        }
        catch (InvalidApiScope $ex2) {
            $this->log_service->error($ex2);
            return $this->error400(array('error'=>$ex2->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function removeRequiredScope($id, $scope_id){
        try {
            $res = $this->api_endpoint_service->removeRequiredScope($id,$scope_id);
            return $res?$this->ok():$this->error400(array('error'=>'operation failed'));
        }
        catch (InvalidApiEndpoint $ex1) {
            $this->log_service->error($ex1);
            return $this->error400(array('error'=>$ex1->getMessage()));
        }
        catch (InvalidApiScope $ex2) {
            $this->log_service->error($ex2);
            return $this->error400(array('error'=>$ex2->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }
}