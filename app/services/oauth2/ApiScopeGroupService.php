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

use auth\IUserRepository;
use oauth2\exceptions\InvalidApiScopeGroup;
use oauth2\models\IApiScopeGroup;
use oauth2\services\IApiScopeGroupService;
use oauth2\repositories\IApiScopeGroupRepository;
use oauth2\services\IApiScopeService;
use utils\exceptions\EntityNotFoundException;
use utils\services\ILogService;
use utils\db\ITransactionService;
use ApiScopeGroup;

/**
 * Class ApiScopeGroupService
 * @package services\oauth2
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


    public function __construct
    (
        IApiScopeGroupRepository $repository,
        IApiScopeService $scope_service,
        IUserRepository $user_repository,
        ITransactionService $tx_service,
        ILogService $log_service
    )
    {
        $this->log_service     = $log_service;
        $this->repository      = $repository;
        $this->user_repository = $user_repository;
        $this->scope_service   = $scope_service;
        $this->tx_service      = $tx_service;
    }

    public function update($id, array $params)
    {
        $repository      = $this->repository;
        $scope_service   = $this->scope_service;
        $user_repository = $this->user_repository;

        return $this->tx_service->transaction(function () use ($id, $params, $repository, $scope_service, $user_repository) {

            $group = ApiScopeGroup::find($id);

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
                        if (ApiScopeGroup::where('name', '=', $params[$param])->where('id', '<>', $id)->count() > 0)
                        {
                            throw new InvalidApiScopeGroup(sprintf('there is already another api scope group name (%s).', $params[$param]));
                        }
                    }
                    if($param === 'scopes')
                    {
                        $ids = $group->scopes()->getRelatedIds();
                        $group->scopes()->detach($ids);
                        $scopes = explode(',', $params['scopes']);
                        foreach($scopes as $scope_id)
                        {
                            $scope = $scope_service->get(intval($scope_id));
                            if(is_null($scope)) throw new EntityNotFoundException(sprintf('scope %s not found.',$scope_id));
                            $group->addScope($scope);
                        }
                    }
                    else if($param === 'users'){
                        $ids = $group->users()->getRelatedIds();
                        $group->users()->detach($ids);
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
        return ApiScopeGroup::find($id)->update(array('active' => $status));
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
                $scope = $scope_service->get(intval($scope_id));
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