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

namespace services\oauth2;

use utils\db\ITransactionService;
use oauth2\repositories\IAssymetricKeyRepository;
use oauth2\models\IAssymetricKey;
use DB;

abstract class AssymetricKeyService
{
    /**
     * @var ITransactionService
     */
    protected $tx_service;

    /**
     * @var IAssymetricKeyRepository
     */
    protected $repository;

    public function __construct(
        IAssymetricKeyRepository $repository,
        ITransactionService $tx_service)
    {
        $this->tx_service        = $tx_service;
        $this->repository        = $repository;
    }

    /**
     * @param array $params
     * @return IAssymetricKey
     */
    abstract public function register(array $params);


    /**
     * @param int $key_id
     * @return bool
     */
    public function delete($key_id)
    {
        $repository = $this->repository;
        return $this->tx_service->transaction(function() use($key_id, $repository) {

            $key = $repository->getById($key_id);
            if(!$key) return false;
            $repository->delete($key);
            return true;
        });
    }

    /**
     * @param int $key_id
     * @param array $params
     * @return bool
     */
    public function update($key_id, array $params)
    {
        $repository = $this->repository;

        return $this->tx_service->transaction(function () use ($key_id, $params, $repository) {

            $key = $repository->getById($key_id);

            if (is_null($key)) {
                return false;
            }

            $allowed_update_params = array(
                'active',
            );

            foreach ($allowed_update_params as $param) {
                if (array_key_exists($param, $params)) {
                    $key->{$param} = $params[$param];
                }
            }

            $repository->add($key);
            return true;
        });
    }


}