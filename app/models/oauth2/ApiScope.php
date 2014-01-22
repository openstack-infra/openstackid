<?php

use  oauth2\models\IApiScope;

class ApiScope extends Eloquent implements IApiScope {

    protected $table = 'oauth2_api_scope';

    public function api()
    {
        return $this->belongsTo('Api');
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

    public function getApiName()
    {
        $api = $this->api()->first();
        return !is_null($api)?$api->name:'';
    }

    public function getApiDescription(){
        $api = $this->api()->first();
        return !is_null($api)? $api->description:'';
    }
}