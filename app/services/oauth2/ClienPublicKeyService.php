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

use oauth2\models\IAssymetricKey;
use oauth2\models\IClientPublicKey;
use oauth2\services\IClienPublicKeyService;
use utils\db\ITransactionService;
use oauth2\repositories\IClientPublicKeyRepository;
use ClientPublicKey;
use oauth2\repositories\IClientRepository;
use DB;
use ValidationException;
/**
 * Class ClienPublicKeyService
 * @package services\oauth2
 */
final class ClienPublicKeyService extends AssymetricKeyService implements IClienPublicKeyService
{

    /**
     * @var IClientRepository
     */
    private $client_repository;

    public function __construct(
        IClientPublicKeyRepository $repository,
        IClientRepository $client_repository,
        ITransactionService $tx_service)
    {

        $this->client_repository = $client_repository;
        parent::__construct($repository, $tx_service);
    }

    /**
     * @param array $params
     * @return IAssymetricKey
     */
    public function register(array $params)
    {
        $client_repository = $this->client_repository;
        $repository = $this->repository;

        return $this->tx_service->transaction(function() use($params, $repository, $client_repository)
        {

            if ($repository->get($params['kid']))
            {
                throw new ValidationException('public key identifier (kid) already exists!.');
            }

            if ($repository->getByPEM($params['pem_content']))
            {
                throw new ValidationException('public key already exists!.');
            }

            $client = $client_repository->get(intval($params['client_id']));

            if(is_null($client))
                throw new ValidationException('client does not exits!');

            $old_key = $client->public_keys()
                ->where('type','=', $params['type'])
                ->where('usage','=', $params['usage'])
                ->where('alg','=', $params['alg'])
                ->where('valid_from','<=',new \DateTime($params['valid_to']))
                ->where('valid_to','>=', new \DateTime($params['valid_from']))
                ->first();

            if (!is_null($old_key))
            {
                throw new ValidationException('public key validity range overlap an existing one!.');
            }

            $public_key = ClientPublicKey::buildFromPEM
            (
                $params['kid'],
                $params['type'],
                $params['usage'],
                $params['pem_content'],
                $params['alg'],
                $params['active'],
                new \DateTime($params['valid_from']),
                new \DateTime($params['valid_to'])
            );

            $client->addPublicKey($public_key);
            return $public_key;
        });
    }

}