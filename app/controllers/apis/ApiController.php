<?php

use oauth2\IResourceServerContext;
use utils\services\ILogService;
use oauth2\services\IApiService;

/**
 * Class ApiController
 * REST controller for Api entity CRUD Ops
 */
class ApiController extends OAuth2ProtectedController implements IRESTController
{

    private $api_service;

    public function __construct(IApiService $api_service,IResourceServerContext $resource_server_context,  ILogService $log_service)
    {
        parent::__construct($resource_server_context,$log_service);
        $this->api_service = $api_service;
    }

    public function get($id)
    {
        try {
            $api = $this->api_service->get($id);
            if(is_null($api)){
                return $this->error404(array('error' => 'api not found'));
            }
            $data = $api->toArray();
            return $this->ok($data);
        } catch (Exception $ex) {
            return $this->error500($ex);
        }
    }

    public function getByPage($page_nbr, $page_size)
    {
        try {
            $list = $this->api_service->getAll($page_size, $page_nbr);
            $items = array();
            foreach ($list->getItems() as $api) {
                array_push($items, $api->toArray());
            }
            return $this->ok( array(
                'page' => $items,
                'total_items' => $list->getTotal()
            ));
        } catch (Exception $ex) {
            return $this->error500($ex);
        }
    }

    public function create()
    {
        try {
            $new_api = Input::all();

            $rules = array(
                'name'               => 'required|max:255',
                'description'        => 'required|text',
                'active'             => 'required|boolean',
                'resource_server_id' => 'required|integer',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($new_api, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error' => $messages));
            }

            $new_api_model = $this->api_service->add(
                $new_api['name'],
                $new_api['description'],
                $new_api['active'],
                $new_api['resource_server_id']
            );

            return $this->ok(array('api_id' => $new_api_model->id));
        } catch (Exception $ex) {
            return $this->error500($ex);
        }
    }

    public function delete($id)
    {
        try {
            $res = $this->api_service->delete($id);
            return $res?Response::json('ok',200):$this->error404(array('error'=>'operation failed'));
        } catch (Exception $ex) {
            return $this->error500($ex);
        }
    }

    public function update(){
        try {

            $values = Input::all();

            $rules = array(
                'id'                 => 'required|integer',
                'name'               => 'required|max:255',
                'description'        => 'required|text',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error' => $messages));
            }

            $api = $this->api_service->get($values['id']);

            if(is_null($api)){
                return $this->error404(array('error'=>'api not found'));
            }

            $api->setName($values['name']);
            $api->setDescription($values['description']);

            $res = $this->api_service->save($api);

            return $res?Response::json('ok',200):$this->error400(array('error'=>'operation failed'));

        } catch (Exception $ex) {
            return $this->error500($ex);
        }
    }

    public function updateStatus($id, $active){
        try {
            $active = is_string($active)?( strtoupper(trim($active))==='TRUE'?true:false ):$active;
            $res    = $this->api_service->setStatus($id,$active);
            return $res?Response::json('ok',200):$this->error400(array('error'=>'operation failed'));
        } catch (Exception $ex) {
            return $this->error500($ex);
        }
    }

    public function assignScope($id,$scope_id){
        try {
            return $this->error400(array('error'=>'operation failed'));
        } catch (Exception $ex) {
            return $this->error500($ex);
        }
    }
} 