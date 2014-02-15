<?php

use oauth2\models\IApi;
use utils\model\BaseModelEloquent;

class Api extends BaseModelEloquent implements IApi {

    protected $fillable = array('name','description','active','resource_server_id','logo');

    protected $table = 'oauth2_api';

	public function getActiveAttribute(){
		return (bool) $this->attributes['active'];
	}

	public function getIdAttribute(){
		return (int) $this->attributes['id'];
	}

	public function getResourceServerIdAttribute(){
		return (int) $this->attributes['resource_server_id'];
	}

    public function scopes()
    {
        return $this->hasMany('ApiScope','api_id');
    }

    public function resource_server()
    {
        return $this->belongsTo('ResourceServer');
    }

    public function endpoints()
    {
        return $this->hasMany('ApiEndpoint','api_id');
    }

    /**
     * @return \oauth2\models\IResourceServer
     */
    public function getResourceServer()
    {
        return $this->resource_server()->first();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLogo()
    {
        $url     = asset('img/apis/server.png');
        return !empty($this->logo)?$this->logo:$url;
    }


    public function getDescription()
    {
        return $this->description;
    }

    public function getScope()
    {
        $scope = '';
        foreach($this->scopes()->get() as $s){
            if(!$s->active) continue;
            $scope = $scope .$s->name.' ';
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
}