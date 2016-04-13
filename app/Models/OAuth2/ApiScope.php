<?php namespace Models\OAuth2;
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
use OAuth2\Models\IApiScope;
use Utils\Model\BaseModelEloquent;
/**
 * Class ApiScope
 * @package Models\OAuth2
 */
class ApiScope extends BaseModelEloquent implements IApiScope
{

    protected $table    = 'oauth2_api_scope';
    protected $hidden   = array ('created_at', 'updated_at', 'pivot');
    protected $fillable = array('name' ,'short_description', 'description','active','default','system', 'api_id', 'assigned_by_groups');

    public function getActiveAttribute(){
        return (bool) $this->attributes['active'];
    }

    public function getDefaultAttribute(){
        return (bool) $this->attributes['default'];
    }

    public function getSystemAttribute(){
        return (bool) $this->attributes['system'];
    }

    public function getIdAttribute(){
        return (int) $this->attributes['id'];
    }

    public function getApiIdAttribute(){
        return (int) $this->attributes['api_id'];
    }

    public function getShortDescription()
    {
        return $this->short_description;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return boolean
     */
    public function isSystem()
    {
        return $this->system;
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    public function api()
    {
        return $this->belongsTo('Models\OAuth2\Api');
    }

    public function getApiName()
    {
        $api = $this->api()->first();
        return !is_null($api)?$api->name:'';
    }

    public function getApiDescription(){
        $api = $this->api()->first();
        return !is_null($api)? $api->description:'';
    }

    public function getApiLogo(){
        $api = $this->api()->first();
        return !is_null($api) ? $api->getLogo():asset('/assets/apis/server.png');
    }

    /**
     * @return \oauth2\models\IApi
     */
    public function getApi()
    {
        return $this->api();
    }

    /**
     * @return bool
     */
    public function isAssignableByGroups()
    {
         return $this->assigned_by_groups;
    }
}