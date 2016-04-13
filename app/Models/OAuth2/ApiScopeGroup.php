<?php namespace Models\OAuth2;
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
use OAuth2\Models\IApiScope;
use OAuth2\Models\IOAuth2User;
use Utils\Model\BaseModelEloquent;
use Utils\Model\IEntity;
/**
 * Class ApiScopeGroup
 * @package Models
 */
class ApiScopeGroup extends BaseModelEloquent implements IEntity
{
    protected $table = 'oauth2_api_scope_group';

    protected $fillable = array('name' ,'description','active');

    public function scopes()
    {
        return $this->belongsToMany('Models\OAuth2\ApiScope','oauth2_api_scope_group_scope','group_id','scope_id');
    }

    public function users()
    {
        return $this->belongsToMany('Auth\User','oauth2_api_scope_group_users','group_id','user_id');
    }

    /**
     * @param IApiScope $scope
     * @return $this
     */
    public function addScope(IApiScope $scope)
    {
        $this->scopes()->attach($scope->id);
        return $this;
    }

    /**
     * @param IOAuth2User $user
     * @return $this
     */
    public function addUser(IOAuth2User $user)
    {
        $this->users()->attach($user->id);
        return $this;
    }

    /**
     * @param IOAuth2User $scope
     * @return $this
     */
    public function removeScope(IOAuth2User $scope)
    {
        $this->scopes()->detach($scope->id);
        return $this;
    }

    /**
     * @return $this
     */
    public function removeAllScopes()
    {
        $this->scopes()->detach();
        return $this;
    }

    /**
     * @param IOAuth2User $user
     * @return $this
     */
    public function removeUser(IOAuth2User $user)
    {
        $this->users()->detach($user->id);
        return $this;
    }

    public function removeAllUsers()
    {
        $this->users()->detach();
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
       return (int)$this->id;
    }
}