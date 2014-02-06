<?php

use oauth2\models\IApiEndpoint;
use utils\model\BaseModelEloquent;

class ApiEndpoint extends BaseModelEloquent implements IApiEndpoint{

    protected $table = 'oauth2_api_endpoint';

    protected $fillable = array('active' , 'description','active','allow_cors', 'name','route', 'http_method', 'api_id');

    public function api()
    {
        return $this->belongsTo('Api');
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function scopes()
    {
        return $this->belongsToMany('ApiScope','oauth2_api_endpoint_api_scope','api_endpoint_id','scope_id');
    }

    public function getHttpMethod(){
        return $this->http_method;
    }

    public function setRoute($route)
    {
        $this->route = $route;
    }

    public function setHttpMethod($http_method)
    {
        $this->http_method = $http_method;
    }

    /**
     * @return \oauth2\models\IApi
     */
    public function getApi()
    {
        return $this->api()->first();
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

    public function setStatus($active)
    {
        $this->active = $active;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name= $name;
    }

    /**
     * @return \oauth2\models\booll
     */
    public function supportCORS()
    {
        return $this->allow_cors;
    }
}