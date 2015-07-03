<?php

use oauth2\models\IApiScope;
use oauth2\models\IOAuth2User;
use utils\model\BaseModelEloquent;
use utils\model\IEntity;

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
class ApiScopeGroup extends BaseModelEloquent implements IEntity
{
    protected $table = 'oauth2_api_scope_group';

    protected $fillable = array('name' ,'description','active');

    public function scopes()
    {
        return $this->belongsToMany('ApiScope','oauth2_api_scope_group_scope','group_id','scope_id');
    }

    public function users()
    {
        return $this->belongsToMany('auth\User','oauth2_api_scope_group_users','group_id','user_id');
    }

    /**
     * @param IApiScope $scope
     */
    public function addScope(IApiScope $scope)
    {
        $this->scopes()->attach($scope->id);
    }

    /**
     * @param IOAuth2User $user
     */
    public function addUser(IOAuth2User $user)
    {
        $this->users()->attach($user->id);
    }

    /**
     * @param IOAuth2User $scope
     */
    public function removeScope(IOAuth2User $scope)
    {
        $this->scopes()->detach($scope->id);
    }

    /**
     * @param IOAuth2User $user
     */
    public function removeUser(IOAuth2User $user)
    {
        $this->users()->detach($user->id);
    }

    /**
     * @return int
     */
    public function getId()
    {
       return (int)$this->id;
    }
}