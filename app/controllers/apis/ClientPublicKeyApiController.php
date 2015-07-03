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
final class ClientPublicKeyApiController extends AssymetricKeyApiController
{
    /**
     * @param IClientPublicKeyRepository $repository
     * @param IClienPublicKeyService $service
     * @param ILogService $log_service
     */
    public function __construct
    (
        IClientPublicKeyRepository $repository,
        IClienPublicKeyService $service,
        ILogService $log_service
    )
    {
        parent::__construct($repository, $service, $log_service);
    }


    /**
     * @param int $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->error404();
    }

    /**
     * @param int $client_id
     * @return mixed
     */
    public function create($client_id)
    {
        try
        {

            $values = Input::All();
            $values['client_id'] = $client_id;
            // Build the validation constraint set.
            $rules = array(
                'client_id'   => 'required|integer',
                'kid'         => 'required|text|max:255',
                'active'      => 'required|boolean',
                'valid_from'  => 'date_format:m/d/Y',
                'valid_to'    => 'date_format:m/d/Y|after:valid_from',
                'pem_content' => 'required|public_key_pem|public_key_pem_length',
                'usage'       => 'required|public_key_usage',
                'type'        => 'required|public_key_type',
                'alg'         => 'required|key_alg:usage',
            );

            // Create a new validator instance.
            $validation = Validator::make($values, $rules);

            if ($validation->fails())
            {
                $messages   = $validation->messages()->toArray();
                return $this->error400(array('error' => 'validation', 'messages' => $messages));
            }

            $public_key = $this->service->register($values);

            return $this->created(array('id' => $public_key->getId()));

        }
        catch(ValidationException $ex1)
        {
            return $this->error400(array('error' => $ex1->getMessage()));
        }
        catch (Exception $ex)
        {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }


    /**
     * @return mixed
     */
    public function getByPage($client_id)
    {
        try {
            //check for optional filters param on querystring
            $fields    = $this->getProjection(Input::get('fields', null));
            $filters   = $this->getFilters(Input::except('fields', 'limit', 'offset'));
            $page_nbr  = intval(Input::get('offset', 1));
            $page_size = intval(Input::get('limit', 10));
            array_push($filters, array
                (
                    'name'  => 'oauth2_client_id',
                    'op'    => '=',
                    'value' => $client_id
                )
            );
            $list = $this->repository->getAll($page_nbr, $page_size, $filters, $fields);
            $items = array();
            foreach ($list->getItems() as $private_key) {
                $data = $private_key->toArray();
                $data['sha_256'] = $private_key->getSHA_256_Thumbprint();
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
     * @param int $client_id
     * @param int $public_key_id
     * @return mixed
     */
    public function update($client_id, $public_key_id)
    {
        return $this->_update($public_key_id);
    }

    /**
     * @param int $client_id
     * @param int $public_key_id
     * @return mixed
     */
    public function delete($client_id, $public_key_id){
        return $this->_delete($public_key_id);
    }

}