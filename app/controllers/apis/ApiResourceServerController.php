<?php

use oauth2\exceptions\InvalidResourceServer;
use oauth2\services\IResourceServerService;
use utils\services\ILogService;

/**
 * Class ApiResourceServerController
 */
class ApiResourceServerController extends AbstractRESTController implements ICRUDController
{
    /**
     * @var IResourceServerService $resource_service
     */
    private $resource_server_service;

    public function __construct(IResourceServerService $resource_server_service, ILogService $log_service)
    {
        parent::__construct($log_service);
        $this->resource_server_service = $resource_server_service;
        $this->allowed_filter_fields = array('');
        $this->allowed_projection_fields = array('*');
    }

    public function get($id)
    {
        try {
            $resource_server = $this->resource_server_service->get($id);
            if (is_null($resource_server)) {
                return $this->error404(array('error' => 'resource server not found'));
            }

            $data = $resource_server->toArray();
            $apis = $resource_server->apis()->get(array('id', 'name'));
            $data['apis'] = $apis->toArray();

            $client = $resource_server->getClient();
            if (!is_null($client)) {
                $data['client_id'] = $client->getClientId();
                $data['client_secret'] = $client->getClientSecret();
            }

            return $this->ok($data);
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    public function getByPage()
    {
        try {
            $fields = $this->getProjection(Input::get('fields', null));
            $filters = $this->getFilters(Input::except('fields', 'limit', 'offset'));
            $page_nbr = intval(Input::get('offset', 1));
            $page_size = intval(Input::get('limit', 10));

            $list = $this->resource_server_service->getAll($page_nbr, $page_size, $filters, $fields);
            $items = array();
            foreach ($list->getItems() as $rs) {
                array_push($items, $rs->toArray());
            }

            return $this->ok(array(
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
                'host' => 'required|host|max:255',
                'ip' => 'required|ip|max:16',
                'friendly_name' => 'required|text|max:512',
                'active' => 'required|boolean',
            );
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error400(array('error' => 'validation', 'messages' => $messages));
            }

            $new_resource_server_model = $this->resource_server_service->add(
                $values['host'],
                $values['ip'],
                $values['friendly_name'],
                $values['active']);

            return $this->created(array('resource_server_id' => $new_resource_server_model->id));
        } catch (InvalidResourceServer $ex1) {
            $this->log_service->error($ex1);

            return $this->error400(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    public function delete($id)
    {
        try {
            $res = $this->resource_server_service->delete($id);

            return $res ? $this->deleted() : $this->error404(array('error' => 'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    public function regenerateClientSecret($id)
    {
        try {
            $res = $this->resource_server_service->regenerateClientSecret($id);

            return !is_null($res) ? $this->ok(array('new_secret' => $res)) : $this->error404(array('error' => 'operation failed'));
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
                'id' => 'required|integer',
                'host' => 'sometimes|required|host|max:255',
                'ip' => 'sometimes|required|ip|max:16',
                'friendly_name' => 'sometimes|required|text|max:512',
            );
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);
            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error400(array('error' => 'validation', 'messages' => $messages));
            }
            $res = $this->resource_server_service->update(intval($values['id']), $values);

            return $res ? $this->ok() : $this->error400(array('error' => 'operation failed'));
        } catch (InvalidResourceServer $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    public function activate($id)
    {
        try {
            $res = $this->resource_server_service->setStatus($id, true);

            return $res ? $this->ok() : $this->error400(array('error' => 'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    public function deactivate($id)
    {
        try {
            $res = $this->resource_server_service->setStatus($id, false);

            return $res ? $this->ok() : $this->error400(array('error' => 'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }
}