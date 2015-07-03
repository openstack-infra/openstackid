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

use oauth2\models\IClientPublicKey;
use oauth2\services\IClienPublicKeyService;
use utils\db\ITransactionService;
use oauth2\repositories\IClientPublicKeyRepository;
use ClientPublicKey;
use oauth2\repositories\IClientRepository;

/**
 * Class ClienPublicKeyService
 * @package services\oauth2
 */
final class ClienPublicKeyService implements IClienPublicKeyService {

    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * @var IClientPublicKeyRepository
     */
    private $repository;

    /**
     * @var IClientRepository
     */
    private $client_repository;

    public function __construct(
        IClientPublicKeyRepository $repository,
        IClientRepository $client_repository,
        ITransactionService $tx_service)
    {
        $this->tx_service        = $tx_service;
        $this->client_repository = $client_repository;
        $this->repository        = $repository;
    }

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return IClientPublicKey[]
     */
    public function getAll($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'))
    {
        DB::getPaginator()->setCurrentPage($page_nbr);
        return ClientPublicKey::Filter($filters)->paginate($page_size, $fields);
    }

    /**
     * @param array $params
     * @return IClientPublicKey
     */
    public function register(array $params)
    {
        $client_repository = $this->client_repository;

        return $this->tx_service->transaction(function() use($params, $client_repository) {
            $public_key = ClientPublicKey::buildFromPEM($params['kid'], $params['type'], $params['usage'], $params['pem_content']);
            $client     = $client_repository->get(intval($params['client_id']));
            $client->addPublicKey($public_key);
            return $public_key;
        });
    }

    /**
     * @param $public_key_id
     * @return bool
     */
    public function delete($public_key_id)
    {
        $repository = $this->repository;
        return $this->tx_service->transaction(function() use($public_key_id, $repository) {

            $public_key = $repository->getById($public_key_id);
            if(!$public_key) return false;
            $repository->delete($public_key);
            return true;
        });
    }
}