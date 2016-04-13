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
use Utils\Model\BaseModelEloquent;
use OAuth2\Models\IApi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * Class Api
 * @package Models\OAuth2
 */
class Api extends BaseModelEloquent implements IApi
{

    protected $fillable = array('name', 'description', 'active', 'resource_server_id', 'logo');

    protected $table = 'oauth2_api';

    public function getActiveAttribute()
    {
        return (bool)$this->attributes['active'];
    }

    public function getIdAttribute()
    {
        return (int)$this->attributes['id'];
    }

    public function getLogoAttribute()
    {
       return $this->getLogo();
    }

    public function getResourceServerIdAttribute()
    {
        return (int)$this->attributes['resource_server_id'];
    }

    public function scopes()
    {
        return $this->hasMany('Models\OAuth2\ApiScope', 'api_id');
    }

    public function resource_server()
    {
        return $this->belongsTo('Models\OAuth2\ResourceServer');
    }

    public function endpoints()
    {
        return $this->hasMany('Models\OAuth2\ApiEndpoint', 'api_id');
    }

    /**
     * @return \oauth2\models\IResourceServer
     */
    public function getResourceServer()
    {
        return Cache::remember
        (
            'resource_server_'.$this->resource_server_id,
            Config::get("cache_regions.region_resource_server_lifetime", 60),
            function() {
                return $this->resource_server()->first();
            }
        );
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLogo()
    {
        $url = asset('/assets/img/apis/server.png');
        return $url;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getScope()
    {
        $scope = '';
        foreach ($this->scopes()->get() as $s) {
            if (!$s->active) {
                continue;
            }
            $scope = $scope . $s->name . ' ';
        }
        $scope = trim($scope);

        return $scope;
    }

    public function isActive()
    {
        return $this->active;
    }


    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setStatus($active)
    {
        $this->active = $active;
    }

    public function delete()
    {
        $endpoints = ApiEndpoint::where('api_id', '=', $this->id)->get();
        foreach ($endpoints as $endpoint) {
            $endpoint->delete();
        }

        $scopes = ApiScope::where('api_id', '=', $this->id)->get();
        foreach ($scopes as $scope) {
            $scope->delete();
        }

        return parent::delete();
    }
}