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
        return $this->api()->first()->name;
    }

    public function getApiDescription(){
        return $this->api()->first()->description;
    }
}