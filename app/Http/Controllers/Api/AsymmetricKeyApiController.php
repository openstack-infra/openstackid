<?php namespace App\Http\Controllers\Api;
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
use OAuth2\Services\IAsymmetricKeyService;
use Utils\Exceptions\EntityNotFoundException;
use Utils\Services\ILogService;
use OAuth2\Repositories\IAsymmetricKeyRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Exception;

class AsymmetricKeyApiController extends AbstractRESTController
{
    /**
     * @var IAsymmetricKeyService
     */
    protected $service;

    /**
     * @var IAsymmetricKeyRepository
     */
    protected $repository;

    /**
     * @param IAsymmetricKeyRepository $repository
     * @param IAsymmetricKeyService $service
     * @param ILogService $log_service
     */
    public function __construct(
        IAsymmetricKeyRepository $repository,
        IAsymmetricKeyService $service,
        ILogService $log_service
    ) {
        parent::__construct($log_service);
        $this->repository = $repository;
        $this->service = $service;
        //set filters allowed values
        $this->allowed_filter_fields = array('*');
        $this->allowed_projection_fields = array('*');
    }

    /**
     * @param $id
     * @return mixed
     */
    protected function _delete($id)
    {
        try {
            $res = $this->service->delete($id);

            return $res ? $this->deleted() : $this->error404(array('error' => 'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }


    protected function _update($id)
    {
        try {

            $values = Input::all();

            $rules = array(
                'id'     => 'required|integer',
                'active' => 'required|boolean',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error400(array('error' => 'validation', 'messages' => $messages));
            }

            $this->service->update(intval($id), $values);

            return $this->ok();

        } catch (EntityNotFoundException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    protected function _getByPage()
    {
        try {
            //check for optional filters param on querystring
            $fields    = $this->getProjection(Input::get('fields', null));
            $filters   = $this->getFilters(Input::except('fields', 'limit', 'offset'));
            $page_nbr  = intval(Input::get('offset', 1));
            $page_size = intval(Input::get('limit', 10));

            $list = $this->repository->getAll($page_nbr, $page_size, $filters, $fields);
            $items = array();
            foreach ($list->items() as $private_key) {
                $data = $private_key->toArray();
                $data['sha_256'] = $private_key->getSHA_256_Thumbprint();
                array_push($items, $data);
            }

            return $this->ok
            (
                array
                (
                    'page'        => $items,
                    'total_items' => $list->total()
                )
            );
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

}