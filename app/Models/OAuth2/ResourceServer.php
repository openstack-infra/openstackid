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

use OAuth2\Models\IResourceServer;
use Utils\Model\BaseModelEloquent;

/**
 * Class ResourceServer
 * @package Models\OAuth2
 */
class ResourceServer extends BaseModelEloquent implements IResourceServer
{

    protected $fillable = array('host', 'ips', 'active', 'friendly_name');

    protected $table = 'oauth2_resource_server';


    public function getActiveAttribute()
    {
        return (bool)$this->attributes['active'];
    }

    public function getIdAttribute()
    {
        return (int)$this->attributes['id'];
    }

    public function apis()
    {
        return $this->hasMany('Models\OAuth2\Api', 'resource_server_id');
    }

    public function client()
    {
        return $this->hasOne('Models\OAuth2\Client');
    }

    /**
     * get resource server host
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * tells if resource server is active or not
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * get resource server friendly name
     * @return mixed
     */
    public function getFriendlyName()
    {
        return $this->friendly_name;
    }

    /**
     * @return \oauth2\models\IClient
     */
    public function getClient()
    {
        return $this->client()->first();
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function setActive($active)
    {
        $this->active = $active;
    }

    public function setFriendlyName($friendly_name)
    {
        $this->friendly_name = $friendly_name;
    }

    /**
     * @param string $ip
     * @return bool
     */
    public function isOwn($ip)
    {
        $ips = explode(',',  $this->ips);
        return in_array($ip, $ips);
    }

    /**
     * @return string
     */
    public function getIPAddresses()
    {
       return $this->ips;
    }
}
