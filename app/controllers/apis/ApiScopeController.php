<?php

use utils\services\ILogService;
use oauth2\services\IApiScopeService;
use oauth2\exceptions\InvalidApi;
use oauth2\exceptions\InvalidApiScope;

/**
 * Class ApiScopeController
 */
class ApiScopeController extends JsonController implements IRESTController {

    private $api_scope_service;

    public function __construct(IApiScopeService $api_scope_service,  ILogService $log_service)
    {
        parent::__construct($log_service);
        $this->api_scope_service = $api_scope_service;
    }

    public function get($id)
    {
        try {
            $scope     = $this->api_scope_service->get($id);
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

    public function getByPage($page_nbr, $page_size)
    {
        try {
            $list = $this->api_scope_service->getAll($page_size, $page_nbr);
            $items = array();
            foreach ($list->getItems() as $scope) {
                array_push($items, $scope->toArray());
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
                'name'               => 'required|scopename|max:255',
                'short_description'  => 'required|text',
                'description'        => 'required|text',
                'active'             => 'required|boolean',
                'default'            => 'required|boolean',
                'system'             => 'required|boolean',
                'api_id'             => 'required|integer',
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
                $values['api_id']
            );

            return $this->ok(array('scope_id' => $new_scope->id));
        }
        catch(InvalidApi $ex1){
            $this->log_service->error($ex1);
            return $this->error404(array('error' => $ex1->getMessage()));
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
            return $res?Response::json('ok',200):$this->error404(array('error'=>'operation failed'));
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

            $res = $this->api_scope_service->update(intval($values['id']),$values);

            return $res?Response::json('ok',200):$this->error400(array('error'=>'operation failed'));

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

    public function updateStatus($id, $active){
        try {
            $res    = $this->api_scope_service->setStatus($id,$active);
            return $res?Response::json('ok',200):$this->error400(array('error'=>'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

}