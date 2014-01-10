<?php

use oauth2\services\IResourceServerService;
use utils\services\ILogService;

/**
 * Class ApiResourceServerController
 */
class ApiResourceServerController extends BaseController
{
    /**
     * @var IResourceServerService $resource_service
     */
    private $resource_server_service;
    private $log_service;

    public function __construct(IResourceServerService $resource_server_service, ILogService $log_service)
    {
        $this->resource_server_service = $resource_server_service;
        $this->log_service = $log_service;
    }

    public function get($id)
    {
        try {
            $resource_server = $this->resource_server_service->get($id);
            if (is_null($resource_server)) {
                return Response::json(array(
                    'error' => 'resource server not found'
                ), 404);

            } else {
                $data    = $resource_server->toArray();
                $client = $resource_server->getClient();
                if(!is_null($client)){
                    $data['client_id']     = $client->getClientId();
                    $data['client_secret'] = $client->getClientSecret();
                }
                return Response::json(
                    $data,
                    200);
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return Response::json(
                array(
                    'error' => 'server error'
                ), 500);
        }
    }

    public function getByPage($page_nbr, $page_size)
    {
        try {
            $list = $this->resource_server_service->getAll($page_size, $page_nbr);
            $items = array();
            foreach ($list->getItems() as $rs) {
                array_push($items, $rs->toArray());
            }
            return Response::json(
                array(
                    'page' => $items,
                    'total_items' => $list->getTotal()
                ), 200);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return Response::json(
                array(
                    'error' => 'server error'
                ), 500);
        }
    }

    public function create()
    {
        try {
            $new_resource_server = Input::all();

            $rules = array(
                'host' => 'required|max:255',
                'ip' => 'required|max:16',
                'friendly_name' => 'required|max:512',
                'active' => 'required',
            );
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($new_resource_server, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return Response::json(
                    array(
                        'error' => $messages), 400);
            }

            $new_resource_server_model = $this->resource_server_service->addResourceServer($new_resource_server['host'],
                $new_resource_server['ip'],
                $new_resource_server['friendly_name'],
                $new_resource_server['active']);

            return Response::json(
                array(
                    'resource_server_id' => $new_resource_server_model->id
                )
                , 200);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return Response::json(
                array(
                    'error' => 'server error'
                ), 500);
        }
    }

    public function delete($id)
    {
        try {
            $res = $this->resource_server_service->delete($id);
            return Response::json('ok',$res?200:404);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return Response::json(
                array(
                    'error' => 'server error'
                ), 500);
        }
    }

    public function regenerateClientSecret($id)
    {
        try {
            $res = $this->resource_server_service->regenerateResourceServerClientSecret($id);
            return Response::json(array('new_secret'=>$res),$res?200:404);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return Response::json(
                array(
                    'error' => 'server error'
                ), 500);
        }
    }

    public function update()
    {
        try {

            $values = Input::all();

            $rules = array(
                'id' => 'required',
                'host' => 'required|max:255',
                'ip' => 'required|max:16',
                'friendly_name' => 'required|max:512',
            );
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return Response::json(
                    array(
                        'error' => $messages), 400);
            }

            $rs = $this->resource_server_service->get($values['id']);

            $rs->setFriendlyName($values['friendly_name']);
            $rs->setHost($values['host']);
            $rs->setIp($values['ip']);

            $this->resource_server_service->save($rs);

            return Response::json('ok',200);

        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return Response::json(
                array(
                    'error' => 'server error'
                ), 500);
        }
    }

    public function updateStatus($id, $active){
        try {
            $active = is_string($active)?( strtoupper(trim($active))==='TRUE'?true:false ):$active;
            $this->resource_server_service->setStatus($id,$active);
            return Response::json('ok',200);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return Response::json(
                array(
                    'error' => 'server error'
                ), 500);
        }
    }

} 