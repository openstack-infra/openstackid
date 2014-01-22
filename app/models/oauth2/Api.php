<?php

use oauth2\models\IApi;

class Api  extends Eloquent implements IApi {

    protected $table = 'oauth2_api';

    public function scopes()
    {
        return $this->hasMany('ApiScope','api_id');
    }

    public function resource_server()
    {
        return $this->belongsTo('ResourceServer');
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
        return $this->logo;
    }

    public function getRoute()
    {
        return $this->route;
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

    public function getHttpMethod(){
        return $this->http_method;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setRoute($route)
    {
        $this->route = $route;
    }

    public function setHttpMethod($http_method)
    {
        $this->http_method = $http_method;
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