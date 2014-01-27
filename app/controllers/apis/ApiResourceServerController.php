<?php

use oauth2\services\IResourceServerService;
use utils\services\ILogService;
use oauth2\exceptions\InvalidResourceServer;
/**
 * Class ApiResourceServerController
 */
class ApiResourceServerController extends JsonController implements IRESTController
{
    /**
     * @var IResourceServerService $resource_service
     */
    private $resource_server_service;

    public function __construct(IResourceServerService $resource_server_service, ILogService $log_service)
    {
        parent::__construct($log_service);
        $this->resource_server_service = $resource_server_service;
    }

    public function get($id)
    {
        try {
            $resource_server = $this->resource_server_service->get($id);
            if (is_null($resource_server)) {
                return $this->error404(array('error' => 'resource server not found'));
            }

            $data         = $resource_server->toArray();
            $apis         = $resource_server->apis()->get(array('id','name'));
            $data['apis'] = $apis->toArray();

            $client  = $resource_server->getClient();
            if(!is_null($client)){
                    $data['client_id']     = $client->getClientId();
                    $data['client_secret'] = $client->getClientSecret();
            }
            return $this->ok($data);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
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
            $values = Input::all();

            $rules = array(
                'host'          => 'required|host|max:255',
                'ip'            => 'required|ip|max:16',
                'friendly_name' => 'required|text|max:512',
                'active'        => 'required|boolean',
            );
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error'=>'validation','messages' => $messages));
            }

            $new_resource_server_model = $this->resource_server_service->add(
                $values['host'],
                $values['ip'],
                $values['friendly_name'],
                $values['active']);

            return $this->ok(array('resource_server_id' => $new_resource_server_model->id));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function delete($id)
    {
        try {
            $res = $this->resource_server_service->delete($id);
            return $res?Response::json('ok',200):$this->error404(array('error'=>'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function regenerateClientSecret($id)
    {
        try {
            $res = $this->resource_server_service->regenerateClientSecret($id);
            return !is_null($res)?Response::json(array('new_secret'=>$res),200):$this->error404(array('error'=>'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function update()
    {
        try {

            $values = Input::all();

            $rules = array(
                'id'            => 'required|integer',
                'host'          => 'sometimes|required|host|max:255',
                'ip'            => 'sometimes|required|ip|max:16',
                'friendly_name' => 'sometimes|required|text|max:512',
            );
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error'=>'validation','messages' => $messages));
            }

            $res = $this->resource_server_service->update(intval($values['id']),$values);

            return $res?Response::json('ok',200):$this->error400(array('error'=>'operation failed'));

        }
        catch(InvalidResourceServer $ex1){
            $this->log_service->error($ex1);
            return $this->error404(array('error'=>'resource server not found'));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function updateStatus($id, $active){
        try {
            $res = $this->resource_server_service->setStatus($id,$active);
            return $res?Response::json('ok',200):$this->error400(array('error'=>'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }
}