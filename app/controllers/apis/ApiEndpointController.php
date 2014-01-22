<?php

use oauth2\IResourceServerContext;
use utils\services\ILogService;
use oauth2\services\IApiEndpointService;

/**
 * Class ApiEndpointController
 * REST Controller for Api endpoint entity CRUD ops
 */
class ApiEndpointController extends OAuth2ProtectedController implements IRESTController {


    private $api_endpoint_service;

    public function __construct(IApiEndpointService $api_endpoint_service,IResourceServerContext $resource_server_context,  ILogService $log_service)
    {
        parent::__construct($resource_server_context,$log_service);
        $this->api_endpoint_service = $api_endpoint_service;
    }

    public function get($id)
    {
        try {
            $api_endpoint = $this->api_endpoint_service->get($id);
            if(is_null($api_endpoint)){
                return $this->error404(array('error' => 'api endpoint not found'));
            }
            $data = $api_endpoint->toArray();
            return $this->ok($data);
        } catch (Exception $ex) {
            return $this->error500($ex);
        }
    }

    public function getByPage($page_nbr, $page_size)
    {
        try {
            $list = $this->api_endpoint_service->getAll($page_size, $page_nbr);
            $items = array();
            foreach ($list->getItems() as $api_endpoint) {
                array_push($items, $api_endpoint->toArray());
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
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($new_api_endpoint, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error' => $messages));
            }

            $new_api_endpoint_model = $this->api_endpoint_service->add(
                $new_api_endpoint['name'],
                $new_api_endpoint['description'],
                $new_api_endpoint['active'],
                $new_api_endpoint['route'],
                $new_api_endpoint['http_method']
            );

            return $this->ok(array('api_endpoint_id' => $new_api_endpoint_model->id));
        } catch (Exception $ex) {
            return $this->error500($ex);
        }
    }

    public function delete($id)
    {

    }

    public function update()
    {

    }
}