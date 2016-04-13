<?php namespace Services\OAuth2;
/**
 * Copyright 2016 OpenStack Foundation
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

use OAuth2\Models\IAsymmetricKey;
use OAuth2\Services\IClientPublicKeyService;
use Utils\Db\ITransactionService;
use OAuth2\Repositories\IClientPublicKeyRepository;
use Models\OAuth2\ClientPublicKey;
use OAuth2\Repositories\IClientRepository;
use Services\Exceptions\ValidationException;
use Utils\Services\IAuthService;
use DB;
use DateTime;

/**
 * Class ClientPublicKeyService
 * @package Services\OAuth2
 */
final class ClientPublicKeyService extends AsymmetricKeyService implements IClientPublicKeyService
{

    /**
     * @var IClientRepository
     */
    private $client_repository;

    /**
     * @var IAuthService
     */
    private $auth_service;

    /**
     * ClientPublicKeyService constructor.
     * @param IClientPublicKeyRepository $repository
     * @param IClientRepository $client_repository
     * @param IAuthService $auth_service
     * @param ITransactionService $tx_service
     */
    public function __construct(
        IClientPublicKeyRepository $repository,
        IClientRepository          $client_repository,
        IAuthService               $auth_service,
        ITransactionService        $tx_service
    )
    {
        parent::__construct($repository, $tx_service);

        $this->client_repository = $client_repository;
        $this->auth_service      = $auth_service;

    }

    /**
     * @param array $params
     * @return IAsymmetricKey
     */
    public function register(array $params)
    {
        $client_repository = $this->client_repository;
        $repository        = $this->repository;
        $auth_service      = $this->auth_service;

        return $this->tx_service->transaction(function() use($params, $repository, $client_repository, $auth_service)
        {

            if ($repository->getByPEM($params['pem_content']))
            {
                throw new ValidationException('public key already exists on another client, choose another one!.');
            }

            $client = $client_repository->get(intval($params['client_id']));

            if(is_null($client))
                throw new ValidationException('client does not exits!');

            $existent_kid = $client->public_keys()->where('kid','=', $params['kid'])->first();

            if ($existent_kid)
            {
                throw new ValidationException('public key identifier (kid) already exists!.');
            }

            $old_key_active = $client->public_keys()
                ->where('type','=', $params['type'])
                ->where('usage','=', $params['usage'])
                ->where('alg','=', $params['alg'])
                ->where('valid_from','<=',new DateTime($params['valid_to']))
                ->where('valid_to','>=', new DateTime($params['valid_from']))
                ->first();

            $public_key = ClientPublicKey::buildFromPEM
            (
                $params['kid'],
                $params['type'],
                $params['usage'],
                $params['pem_content'],
                $params['alg'],
                $old_key_active ? false : $params['active'],
                new DateTime($params['valid_from']),
                new DateTime($params['valid_to'])
            );

            $client->addPublicKey($public_key);
            $client->setEditedBy($auth_service->getCurrentUser());
            $client_repository->add($client);
            return $public_key;
        });
    }

}