<?php

use oauth2\models\IResourceServer;
use utils\model\BaseModelEloquent;

/**
 * Class ResourceServer
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
        return $this->hasMany('Api', 'resource_server_id');
    }

    public function client()
    {
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
