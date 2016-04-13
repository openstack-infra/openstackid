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

use Illuminate\Support\Facades\DB;
use OAuth2\Exceptions\InvalidResourceServer;
use OAuth2\Models\IClient;
use OAuth2\Models\IResourceServer;
use OAuth2\Services\IClientService;
use OAuth2\Services\IResourceServerService;
use Models\OAuth2\ResourceServer;
use Utils\Db\ITransactionService;

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
     * @param IClientService $client_service
     * @param ITransactionService $tx_service
     */
    public function __construct(IClientService $client_service, ITransactionService $tx_service)
    {
        $this->client_service = $client_service;
        $this->tx_service     = $tx_service;
    }

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'))
    {
        return ResourceServer::Filter($filters)->paginate($page_size, $fields, $pageName ='Page', $page_nbr);
    }

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws InvalidResourceServer
     */
    public function update($id, array $params)
    {

        $res = false;
        $this_var = $this;

        $this->tx_service->transaction(function () use ($id, $params, &$res, &$this_var) {

            $resource_server = ResourceServer::find($id);

            if (is_null($resource_server)) {
                throw new InvalidResourceServer(sprintf('resource server id %s does not exists!', $id));
            }
            $allowed_update_params = array('host', 'ips', 'active', 'friendly_name');

            foreach ($allowed_update_params as $param) {
                if (array_key_exists($param, $params)) {

                    if ($param == 'host') {
                        if (ResourceServer::where('host', '=', $params[$param])->where('id', '<>', $id)->count() > 0) {
                            throw new InvalidResourceServer(sprintf('there is already another resource server with that hostname (%s).',
                                $params[$param]));
                        }
                    }

                    if ($param == 'friendly_name') {
                        if (ResourceServer::where('friendly_name', '=', $params[$param])->where('id', '<>',
                                $id)->count() > 0
                        ) {
                            throw new InvalidResourceServer(sprintf('there is already another resource server with that friendly name (%s).',
                                $params[$param]));
                        }
                    }

                    $resource_server->{$param} = $params[$param];
                }
            }
            $res = $this_var->save($resource_server);
        });

        return $res;
    }

    /**
     * @param IResourceServer $resource_server
     * @return bool
     */
    public function save(IResourceServer $resource_server)
    {
        if (!$resource_server->exists() || count($resource_server->getDirty()) > 0) {
            return $resource_server->Save();
        }

        return true;
    }

    /**
     * sets resource server status (active/deactivated)
     * @param $id id of resource server
     * @param bool $active status (active/non active)
     * @return bool
     */
    public function setStatus($id, $active)
    {
        return ResourceServer::find($id)->update(array('active' => $active));
    }

    /**
     * deletes a resource server
     * @param id $id
     * @return bool
     */
    public function delete($id)
    {
        $res = false;
        $client_service = $this->client_service;

        $this->tx_service->transaction(function () use ($id, &$res, &$client_service) {

            $resource_server = ResourceServer::find($id);

            if (!is_null($resource_server)) {
                $client = $resource_server->client()->first();
                if (!is_null($client)) {
                    $client_service->deleteClientByIdentifier($client->id);
                }
                $resource_server->delete();
                $res = true;
            }
        });

        return $res;
    }

    /**
     * get a resource server by id
     * @param $id id of resource server
     * @return IResourceServer
     */
    public function get($id)
    {
        return ResourceServer::find($id);
    }

    /** Creates a new resource server instance
     * @param $host
     * @param $ips
     * @param $friendly_name
     * @param bool $active
     * @return IResourceServer
     */
    public function add($host, $ips, $friendly_name, $active)
    {

        $client_service = $this->client_service;

        if (is_string($active))
        {
            $active = strtoupper($active) == 'TRUE' ? true : false;
        }

        return $this->tx_service->transaction(function () use (
            $host,
            $ips,
            $friendly_name,
            $active,
            $client_service
        ) {

            if (ResourceServer::where('host', '=', $host)->count() > 0) {
                throw new InvalidResourceServer(sprintf('there is already another resource server with that hostname (%s).',
                    $host));
            }

            if (ResourceServer::where('ips','like', '%'.$ips.'%')->count() > 0)
            {
                throw new InvalidResourceServer(sprintf('there is already another resource server with that ip (%s).',
                    $ips));
            }

            if (ResourceServer::where('friendly_name', '=', $friendly_name)->count() > 0) {
                throw new InvalidResourceServer(sprintf('there is already another resource server with that friendly name (%s).',
                    $friendly_name));
            }

            $instance = new ResourceServer
            (
                array
                (
                    'host'          => $host,
                    'ips'           => $ips,
                    'active'        => $active,
                    'friendly_name' => $friendly_name
                )
            );

            $instance->Save();

            // creates a new client for this brand new resource server
            $new_client = $client_service->register
            (
                IClient::ApplicationType_Service,
                null,
                $host . '.confidential.application',
                $friendly_name . ' confidential oauth2 application', ''
            );

            $new_client->resource_server()->associate($instance);
            // does not expires ...
            $new_client->client_secret_expires_at = null;
            $new_client->Save();
            return $instance;
        });

    }

    /**
     * @param $id
     * @return bool
     */
    public function regenerateClientSecret($id)
    {
        $res = null;
        $client_service = $this->client_service;

        $this->tx_service->transaction(function () use ($id, &$res, &$client_service) {

            $resource_server = ResourceServer::find($id);

            if (!is_null($resource_server)) {
                $client = $resource_server->client()->first();
                if (!is_null($client)) {
                    $res = $client_service->regenerateClientSecret($client->id);
                }
            }
        });

        return $res;
    }

    /**
     * @param string $ip
     * @return IResourceServer
     */
    public function getByIPAddress($ip)
    {
        return ResourceServer::where('ips','like', '%'.$ip.'%')->first();
    }
}
