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

use Models\OAuth2\ApiScope;
use Illuminate\Support\Facades\DB;
use OAuth2\Exceptions\InvalidApi;
use OAuth2\Exceptions\InvalidApiScope;
use OAuth2\Models\IApiScope;
use OAuth2\Repositories\IApiRepository;
use OAuth2\Repositories\IApiScopeRepository;
use OAuth2\Services\IApiScopeService;
use Utils\Db\ITransactionService;
use Utils\Exceptions\EntityNotFoundException;

/**
 * Class ApiScopeService
 * @package Services\OAuth2
 */
final class ApiScopeService implements IApiScopeService
{

    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * @var IApiScopeRepository
     */
    private $repository;

    /**
     * @var IApiRepository
     */
    private $api_repository;

    /**
     * ApiScopeService constructor.
     * @param ITransactionService $tx_service
     * @param IApiScopeRepository $repository
     * @param IApiRepository $api_repository
     */
    public function __construct
    (
        ITransactionService $tx_service,
        IApiScopeRepository $repository,
        IApiRepository $api_repository
    )
    {
        $this->tx_service     = $tx_service;
        $this->repository     = $repository;
        $this->api_repository = $api_repository;
    }

    /**
     * @param array $scopes_names
     * @return mixed
     */
    public function getFriendlyScopesByName(array $scopes_names)
    {
        return DB::table('oauth2_api_scope')->where('active', '=', true)->whereIn('name',
            $scopes_names)->pluck('short_description')->all();
    }

    /**
     * @param bool $system
     * @param bool $assigned_by_groups
     * @return array|mixed
     */
    public function getAvailableScopes($system = false, $assigned_by_groups = false)
    {
        $res    = [];
        $scopes = $this->repository->getActives();

        foreach ($scopes as $scope)
        {
            $api = $scope->api()->first();
            if (!is_null($api) && $api->resource_server()->first()->active && $api->active) {
                if ($scope->system && !$system) {
                    continue;
                }
                if ($scope->assigned_by_groups && !$assigned_by_groups) {
                    continue;
                }
                $res[] = $scope;
            }
        }

        return $res;
    }

    /**
     * @param array $scopes_names
     * @return array|mixed
     */
    public function getAudienceByScopeNames(array $scopes_names)
    {
        $scopes   = $this->repository->getByName($scopes_names);
        $audience = [];
        foreach ($scopes as $scope) {
            $api = $scope->getApi();
            $resource_server = !is_null($api) ? $api->getResourceServer() : null;
            if (!is_null($resource_server) && !array_key_exists($resource_server->host, $audience)) {
                $audience[$resource_server->host] = $resource_server->ip;
            }
        }

        return $audience;
    }

    /**
     * @param array $scopes_names
     * @return string
     */
    public function getStrAudienceByScopeNames(array $scopes_names)
    {
        $audiences = $this->getAudienceByScopeNames($scopes_names);
        $audience = '';
        foreach ($audiences as $resource_server_host => $ip) {
            $audience = $audience . $resource_server_host . ' ';
        }
        $audience = trim($audience);

        return $audience;
    }

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws InvalidApiScope
     * @throws EntityNotFoundException
     */
    public function update($id, array $params)
    {

        return $this->tx_service->transaction(function () use ($id, $params) {

            //check that scope exists...
            $scope = $this->repository->get($id);
            if (is_null($scope)) {
                throw new EntityNotFoundException(sprintf('scope id %s does not exists!', $id));
            }

            $allowed_update_params = array('name', 'description', 'short_description', 'active', 'system', 'default', 'assigned_by_groups');

            foreach ($allowed_update_params as $param) {
                if (array_key_exists($param, $params)) {

                    if ($param == 'name') {
                        //check if we have a former scope with selected name
                        $former_scope = $this->repository->getFirstByName($params[$param]);

                        if (!is_null($former_scope) && $former_scope->id != $id) {
                            throw new InvalidApiScope(sprintf('scope name %s already exists!', $params[$param]));
                        }
                    }

                    $scope->{$param} = $params[$param];
                }
            }

            $this->repository->add($scope);
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
        return $this->tx_service->transaction(function() use($id, $active){
            //check that scope exists...
            $scope = $this->repository->get($id);
            if (is_null($scope)) {
                throw new EntityNotFoundException(sprintf('scope id %s does not exists!', $id));
            }
            $scope->active = $active;
            $this->repository->add($scope);
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
            $scope = $this->repository->get($id);
            if (is_null($scope)) {
                throw new EntityNotFoundException(sprintf('scope id %s does not exists!', $id));
            }
            $this->repository->delete($scope);
            return true;
        });
    }

    /**
     * Creates a new api scope instance
     * @param $name
     * @param $short_description
     * @param $description
     * @param $active
     * @param $default
     * @param $system
     * @param $api_id
     * @param $assigned_by_groups
     * @throws InvalidApi
     * @return IApiScope
     */
    public function add($name, $short_description, $description, $active, $default, $system, $api_id, $assigned_by_groups)
    {
        $instance = null;
        $this->tx_service->transaction(function () use (
            $name,
            $short_description,
            $description,
            $active,
            $default,
            $system,
            $api_id,
            $assigned_by_groups,
            &$instance
        ) {

            $api = $this->api_repository->get($api_id);
            // check if api exists...
            if (is_null($api)) {
                throw new InvalidApi(sprintf('api id %s does not exists!.', $api_id));
            }

            $former_scopes = $this->repository->getByName([$name]);
            //check if we have a former scope with selected name
            if ($former_scopes->count() > 0) {
                throw new InvalidApiScope(sprintf('scope name %s not allowed.', $name));
            }
            // todo : move to factory
            $instance = new ApiScope
            (
                array
                (
                    'name'               => $name,
                    'description'        => $description,
                    'short_description'  => $short_description,
                    'active'             => $active,
                    'default'            => $default,
                    'system'             => $system,
                    'api_id'             => $api_id,
                    'assigned_by_groups' => $assigned_by_groups
                )
            );

            $this->repository->add($instance);
        });

        return $instance;
    }

    /**
     * @return mixed
     */
    public function getAssignedByGroups()
    {
        $res = [];
        foreach ($this->repository->getAssignableByGroups() as $scope)
        {
            $api = $scope->api()->first();
            if (!is_null($api) && $api->resource_server()->first()->active && $api->active) {
                $res[] = $scope;
            }
        }

        return $res;
    }
}