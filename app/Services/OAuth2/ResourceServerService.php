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

use Models\OAuth2\ResourceServer;
use OAuth2\Exceptions\InvalidResourceServer;
use OAuth2\Models\IClient;
use OAuth2\Models\IResourceServer;
use OAuth2\Repositories\IClientRepository;
use OAuth2\Repositories\IResourceServerRepository;
use OAuth2\Services\IClientService;
use OAuth2\Services\IResourceServerService;
use Utils\Db\ITransactionService;
use Utils\Exceptions\EntityNotFoundException;

/**
 * Class ResourceServerService
 * @package Services\OAuth2
 */
final class ResourceServerService implements IResourceServerService
{

    /**
     * @var IClientService
     */
    private $client_service;

    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * @var IResourceServerRepository
     */
    private $repository;

    /**
     * @var IClientRepository
     */
    private $client_repository;

    /**
     * ResourceServerService constructor.
     * @param IClientService $client_service
     * @param IClientRepository $client_repository
     * @param IResourceServerRepository $repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        IClientService $client_service,
        IClientRepository $client_repository,
        IResourceServerRepository $repository,
        ITransactionService $tx_service
    )
    {
        $this->client_service = $client_service;
        $this->repository = $repository;
        $this->client_repository = $client_repository;
        $this->tx_service = $tx_service;
    }

    /**
     * @param string $host
     * @param string $ips
     * @param string $friendly_name
     * @param bool $active
     * @return IResourceServer
     * @throws InvalidResourceServer
     */
    public function add($host, $ips, $friendly_name, $active)
    {

        $client_service = $this->client_service;

        if (is_string($active)) {
            $active = strtoupper($active) == 'TRUE' ? true : false;
        }

        return $this->tx_service->transaction(function () use (
            $host,
            $ips,
            $friendly_name,
            $active,
            $client_service
        ) {

            if ($this->repository->getByHost($host) != null) {
                throw new InvalidResourceServer
                (
                    sprintf('there is already another resource server with that hostname (%s).',$host)
                );
            }

            if ($this->repository->getByIp($ips) != null) {
                throw new InvalidResourceServer
                (
                    sprintf('there is already another resource server with that ip (%s).', $ips)
                );
            }

            if ($this->repository->getByFriendlyName($friendly_name) != null) {
                throw new InvalidResourceServer
                (
                    sprintf('there is already another resource server with that friendly name (%s).', $friendly_name)
                );
            }

            // todo : move to factory
            $instance = new ResourceServer
            (
                [
                    'host'          => $host,
                    'ips'           => $ips,
                    'active'        => $active,
                    'friendly_name' => $friendly_name
                ]
            );

            $this->repository->add($instance);

            // creates a new client for this brand new resource server
            $new_client = $client_service->register
            (
                IClient::ApplicationType_Service,
                $host . '.confidential.application',
                $friendly_name . ' confidential oauth2 application'
            );

            $new_client->resource_server()->associate($instance);
            // does not expires ...
            $new_client->client_secret_expires_at = null;
            $this->client_repository->add($new_client);
            return $instance;
        });

    }

    /**
     * @param int $id
     * @param array $params
     * @return bool
     * @throws InvalidResourceServer
     * @throws EntityNotFoundException
     */
    public function update($id, array $params)
    {


        return $this->tx_service->transaction(function () use ($id, $params) {

            $resource_server = $this->repository->get($id);

            if (is_null($resource_server)) {
                throw new EntityNotFoundException(sprintf('resource server id %s does not exists!', $id));
            }

            $allowed_update_params = array('host', 'ips', 'active', 'friendly_name');

            foreach ($allowed_update_params as $param) {
                if (array_key_exists($param, $params)) {

                    if ($param == 'host') {
                        $former_resource_server = $this->repository->getByHost($params[$param]);
                        if (!is_null($former_resource_server) && $former_resource_server->id != $id) {
                            throw new InvalidResourceServer
                            (
                                sprintf('there is already another resource server with that hostname (%s).',$params[$param])
                            );
                        }
                    }

                    if ($param == 'friendly_name') {
                        $former_resource_server = $this->repository->getByFriendlyName($params[$param]);
                        if (!is_null($former_resource_server) && $former_resource_server->id != $id) {
                            throw new InvalidResourceServer
                            (
                                sprintf('there is already another resource server with that friendly name (%s).', $params[$param])
                            );
                        }
                    }

                    $resource_server->{$param} = $params[$param];
                }
            }
            $this->repository->add($resource_server);
            return true;
        });
    }


    /**
     * @param int $id
     * @param bool $active
     * @return bool
     * @throws EntityNotFoundException
     */
    public function setStatus($id, $active)
    {
        return $this->tx_service->transaction(function () use ($id, $active) {
            $resource_server = $this->repository->get($id);

            if (is_null($resource_server)) {
                throw new EntityNotFoundException(sprintf('resource server id %s does not exists!', $id));
            }
            $resource_server->active = $active;
            $this->repository->add($resource_server);
            return true;
        });
    }

    /**
     * @param int $id
     * @return bool
     * @throws EntityNotFoundException
     */
    public function delete($id)
    {


        return $this->tx_service->transaction(function () use ($id) {

            $resource_server = $this->repository->get($id);

            if (is_null($resource_server)) {
                throw new EntityNotFoundException(sprintf('resource server id %s does not exists!', $id));
            }

            $client = $resource_server->client()->first();
            if (!is_null($client)) {
                $this->client_repository->delete($client);
            }

            $this->repository->delete($resource_server);
            return true;
        });

    }

    /**
     * @param int $id
     * @return string
     * @throws EntityNotFoundException
     */
    public function regenerateClientSecret($id)
    {

        return $this->tx_service->transaction(function () use ($id) {

            $resource_server = $this->repository->get($id);

            if (is_null($resource_server)) {
                throw new EntityNotFoundException(sprintf('resource server id %s does not exists!', $id));
            }

            $client = $resource_server->client()->first();
            if (is_null($client))
                throw new EntityNotFoundException(sprintf('client not found for resource server id %s!', $id));

            $client = $this->client_service->regenerateClientSecret($client->id);

            return $client->getClientSecret();

        });
    }

}
