<?php

use oauth2\IResourceServerContext;
use utils\services\ILogService;
use oauth2\services\IApiService;

class ApiEndpointsController extends OAuth2ProtectedController {

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
                return $this->error404(array('error' => 'endpoint not found'));
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
            foreach ($list->getItems() as $endpoint) {
                array_push($items, $endpoint->toArray());
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
            $new_api_endpoint = Input::all();

            $rules = array(
                'name'               => 'required|max:255',
                'description'        => 'required',
                'active'             => 'required',
                'route'              => 'required',
                'http_method'        => 'required',
                'resource_server_id' => 'required|integer',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($new_api_endpoint, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error' => $messages));
            }

            $new_api_endpoint_model = $this->api_service->addApiEndpoint(
                $new_api_endpoint['name'],
                $new_api_endpoint['description'],
                $new_api_endpoint['active'],
                $new_api_endpoint['route'],
                $new_api_endpoint['http_method'],
                $new_api_endpoint['resource_server_id']
            );

            return $this->ok(array('api_endpoint_id' => $new_api_endpoint_model->id));
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
                'description'        => 'required',
                'route'              => 'required',
                'http_method'        => 'required',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error' => $messages));
            }

            $endpoint = $this->api_service->get($values['id']);

            if(is_null($endpoint)){
                return $this->error404(array('error'=>'endpoint api not found'));
            }

            $endpoint->setName($values['name']);
            $endpoint->setDescription($values['description']);
            $endpoint->setRoute($values['route']);
            $endpoint->setHttpMethod($values['http_method']);

            $res = $this->api_service->save($endpoint);

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
} 