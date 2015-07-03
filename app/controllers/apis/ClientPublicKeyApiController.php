<?php
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
use oauth2\services\IClienPublicKeyService;
use utils\services\ILogService;
use oauth2\repositories\IClientPublicKeyRepository;
/**
 * Class ClientPublicKeyApiController
 */
class ClientPublicKeyApiController extends AbstractRESTController implements ICRUDController
{
    /**
     * @var IClienPublicKeyService
     */
    private $public_key_service;

    /**
     * @var IClientPublicKeyRepository
     */
    private $repository;

    public function __construct(
        IClientPublicKeyRepository $repository,
        IClienPublicKeyService $public_key_service,
        ILogService $log_service
    ) {
        parent::__construct($log_service);

        $this->public_key_service = $public_key_service;
        $this->repository = $repository;
        //set filters allowed values
        $this->allowed_filter_fields = array('*');
        $this->allowed_projection_fields = array('*');
    }


    /**
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->error404();
    }

    /**
     * @return mixed
     */
    public function create()
    {
        $args = func_get_args();
        $client_id = (int)$args[0];

        try {

            $values = Input::All();
            $values['client_id'] = $client_id;
            // Build the validation constraint set.
            $rules = array(
                'client_id'   => 'required|integer',
                'kid'         => 'required|free_text|max:255',
                'pem_content' => 'required|public_key_pem',
                'usage'       => 'required|public_key_usage',
                'type'        => 'required|public_key_type',
            );

            // Create a new validator instance.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error400(array('error' => 'validation', 'messages' => $messages));
            }

            if ($this->repository->get($values['kid'])) {
                return $this->error400(array('error' => 'public key identifier already exists!.'));
            }

            $public_key = $this->public_key_service->register($values);

            return $this->created(array('id' => $public_key->getId()));

        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function getByPage()
    {
        try {
            //check for optional filters param on querystring
            $fields    = $this->getProjection(Input::get('fields', null));
            $filters   = $this->getFilters(Input::except('fields', 'limit', 'offset'));
            $page_nbr  = intval(Input::get('offset', 1));
            $page_size = intval(Input::get('limit', 10));

            $list = $this->public_key_service->getAll($page_nbr, $page_size, $filters, $fields);
            $items = array();
            foreach ($list->getItems() as $public_key) {
                $data = $public_key->toArray();
                array_push($items, $data);
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

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        try {
            $res = $this->public_key_service->delete($id);

            return $res ? $this->deleted() : $this->error404(array('error' => 'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function update()
    {
        return $this->error404();
    }
}