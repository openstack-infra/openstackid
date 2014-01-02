<?php

class ResourceServer extends Eloquent {

    protected $table = 'oauth2_resource_server';

    public function apis()
    {
        return $this->hasMany('Api','resource_server_id');
    }
}
