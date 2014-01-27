<?php

use oauth2\IResourceServerContext;
use utils\services\ILogService;
use oauth2\services\IApiService;
use  oauth2\exceptions\InvalidApi;

/**
 * Class ApiController
 * REST controller for Api entity CRUD Ops
 */
class ApiController extends AbstractRESTController implements IRESTController
{

    private $api_service;


    public function __construct(IApiService $api_service,  ILogService $log_service)
    {
        parent::__construct($log_service);
        $this->api_service = $api_service;
        //set filters allowed values
        $this->allowed_filter_fields = array('resource_server_id');
        $this->allowed_filter_op     = array('resource_server_id' => array('='));
        $this->allowed_filter_value  = array('resource_server_id' => '/^\d+$/');
    }

    public function get($id)
    {
        try {
            $api       = $this->api_service->get($id);
            if(is_null($api)){
                return $this->error404(array('error' => 'api not found'));
            }
            $scopes    = $api->scopes()->get(array('id','name'));
            $endpoints = $api->endpoints()->get(array('id','name'));
            $data = $api->toArray();
            $data['scopes']    = $scopes->toArray();
            $data['endpoints'] = $endpoints->toArray();
            return $this->ok($data);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function getByPage($page_nbr, $page_size)
    {
        try {
            //check for optional filters param on querystring
            $filters = Input::get('filters',null);
            $list    = $this->api_service->getAll($page_nbr,$page_size, $this->getFilters($filters));
            $items   = array();
            foreach ($list->getItems() as $api) {
                array_push($items, $api->toArray());
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
            $new_api = Input::all();

            $rules = array(
                'name'               => 'required|alpha_dash|max:255',
                'description'        => 'required|text',
                'active'             => 'required|boolean',
                'resource_server_id' => 'required|integer',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($new_api, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error'=>'validation','messages' => $messages));
            }

            $new_api_model = $this->api_service->add(
                $new_api['name'],
                $new_api['description'],
                $new_api['active'],
                $new_api['resource_server_id']
            );

            return $this->ok(array('api_id' => $new_api_model->id));
        }
        catch (InvalidApi $ex1) {
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
            $res = $this->api_service->delete($id);
            return $res?Response::json('ok',200):$this->error404(array('error'=>'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function update(){
        try {

            $values = Input::all();

            $rules = array(
                'id'                 => 'required|integer',
                'name'               => 'sometimes|required|alpha_dash|max:255',
                'description'        => 'sometimes|required|text',
                'active'             => 'sometimes|required|boolean',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error'=>'validation','messages' => $messages));
            }

            $res = $this->api_service->update(intval($values['id']),$values);

            return $res?Response::json('ok',200):$this->error400(array('error'=>'operation failed'));

        }
        catch(InvalidApi $ex1){
            $this->log_service->error($ex1);
            return $this->error404(array('error'=>$ex1->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function updateStatus($id, $active){
        try {
            $active = is_string($active)?( strtoupper(trim($active))==='TRUE'?true:false ):$active;
            $res    = $this->api_service->setStatus($id,$active);
            return $res?Response::json('ok',200):$this->error400(array('error'=>'operation failed'));
        }
        catch(InvalidApi $ex1){
            $this->log_service->error($ex1);
            return $this->error404(array('error'=>$ex1->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }
}