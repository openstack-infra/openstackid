<?php

use oauth2\models\IResourceServer;
use oauth2\models\IClient;

class ResourceServer extends Eloquent implements IResourceServer {

    protected $table = 'oauth2_resource_server';

    public function apis()
    {
        return $this->hasMany('Api','resource_server_id');
    }

    public function client(){
        return $this->hasOne('Client');
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
     * get resource server ip address
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
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

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function setFriendlyName($friendly_name)
    {
        $this->friendly_name = $friendly_name;
    }
}
