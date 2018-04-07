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

use Auth\Repositories\IUserRepository;
use OAuth2\Exceptions\InvalidApiScopeGroup;
use OAuth2\Models\IApiScopeGroup;
use OAuth2\Repositories\IApiScopeRepository;
use OAuth2\Services\IApiScopeGroupService;
use OAuth2\Repositories\IApiScopeGroupRepository;
use OAuth2\Services\IApiScopeService;
use Utils\Exceptions\EntityNotFoundException;
use Utils\Services\ILogService;
use Utils\Db\ITransactionService;
use Models\OAuth2\ApiScopeGroup;

/**
 * Class ApiScopeGroupService
 * @package Services\OAuth2
 */
final class ApiScopeGroupService implements IApiScopeGroupService
{

    /**
     * @var IApiScopeGroupRepository
     */
    private $repository;

    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * @var ILogService
     */
    private $log_service;

    /**
     * @var IUserRepository
     */
    private $user_repository;

    /**
     * @var IApiScopeService
     */
    private $scope_service;

    /**
     * @var IApiScopeRepository
     */
    private $scope_repository;


    public function __construct
    (
        IApiScopeGroupRepository $repository,
        IApiScopeService $scope_service,
        IUserRepository $user_repository,
        IApiScopeRepository $scope_repository,
        ITransactionService $tx_service,
        ILogService $log_service
    )
    {
        $this->log_service      = $log_service;
        $this->repository       = $repository;
        $this->user_repository  = $user_repository;
        $this->scope_service    = $scope_service;
        $this->scope_repository = $scope_repository;
        $this->tx_service       = $tx_service;
    }

    public function update($id, array $params)
    {
        $repository      = $this->repository;
        $scope_service   = $this->scope_service;
        $user_repository = $this->user_repository;

        return $this->tx_service->transaction(function () use ($id, $params, $repository, $scope_service, $user_repository) {

            $group = $repository->get($id);

            if (is_null($group))
            {
                throw new InvalidApiScopeGroup(sprintf('api scope group id %s does not exists!', $id));
            }

            $allowed_update_params = array('name', 'active', 'description', 'users', 'scopes');

            foreach ($allowed_update_params as $param)
            {
                if (array_key_exists($param, $params))
                {

                    if ($param == 'name')
                    {
                        $older_group = $repository->getByName($params[$param]);
                        if(!is_null($older_group) && $older_group->id != $id)
                        {
                            throw new InvalidApiScopeGroup(sprintf('there is already another api scope group name (%s).', $params[$param]));
                        }
                    }
                    if($param === 'scopes')
                    {
                        $ids = $group->scopes()->getRelatedIds()->all();
                        $group->scopes()->detach($ids);
                        $scopes = explode(',', $params['scopes']);
                        foreach($scopes as $scope_id)
                        {
                            $scope = $this->scope_repository->get(intval($scope_id));
                            if(is_null($scope)) throw new EntityNotFoundException(sprintf('scope %s not found.',$scope_id));
                            $group->addScope($scope);
                        }
                    }
                    else if($param === 'users'){
                        $group->removeAllUsers();
                        $users = explode(',', $params['users']);
                        foreach($users as $user_id)
                        {
                            $user = $user_repository->get(intval($user_id));
                            if(is_null($user)) throw new EntityNotFoundException(sprintf('user %s not found.',$user_id));
                            $group->addUser($user);
                        }
                    }
                    else
                        $group->{$param} = $params[$param];
                }
            }
            $repository->add($group);
            return true;
        });
    }

    /**
     * @param int $id
     * @param bool $status status (active/non active)
     * @return void
     */
    public function setStatus($id, $status)
    {
        $this->tx_service->transaction(function() use($id, $status){
            $group = $this->repository->get($id);
            if(is_null($group)) return;
            $group->active = $status;
            $this->repository->add($group);
        });
    }

    /**
     * @param string $name
     * @param bool $active
     * @param string $scopes
     * @param string $users
     * @return IApiScopeGroup
     */
    public function register($name, $active, $scopes, $users)
    {
        $repository      = $this->repository;
        $scope_service   = $this->scope_service;
        $user_repository = $this->user_repository;

        return $this->tx_service->transaction(function () use (
            $name,
            $active,
            $scopes,
            $users,
            $repository,
            $scope_service,
            $user_repository
        ) {

            if (ApiScopeGroup::where('name', '=', trim($name))->count() > 0)
            {
                throw new InvalidApiScopeGroup(sprintf('there is already another group with that name (%s).', $name));
            }

            // todo : move to factory
            $repository->add($instance = new ApiScopeGroup
            (
                array
                (
                    'name'        => trim($name),
                    'active'      => $active,
                    'description' => ''
                )
            ));

            $scopes = explode(',', $scopes);
            $users  = explode(',', $users);
            foreach($scopes as $scope_id)
            {
                $scope = $this->scope_repository->get(intval($scope_id));
                if(is_null($scope)) throw new EntityNotFoundException(sprintf('scope %s not found.',$scope_id));
                $instance->addScope($scope);
            }
            foreach($users as $user_id)
            {
                $user = $user_repository->get(intval($user_id));
                if(is_null($user)) throw new EntityNotFoundException(sprintf('user %s not found.',$user_id));
                $instance->addUser($user);
            }
            return $instance;
        });
    }
}