<?php

use oauth2\models\IApiScope;
use utils\model\BaseModelEloquent;

/**
 * Class ApiScope
 */
class ApiScope extends BaseModelEloquent implements IApiScope
{

    protected $table = 'oauth2_api_scope';

    protected $fillable = array('name' ,'short_description', 'description','active','default','system', 'api_id', 'assigned_by_groups');

    public function getActiveAttribute(){
        return (bool) $this->attributes['active'];
    }

    public function getDefaultAttribute(){
        return (bool) $this->attributes['default'];
    }

    public function getSystemAttribute(){
        return (bool) $this->attributes['system'];
    }

    public function getIdAttribute(){
        return (int) $this->attributes['id'];
    }

    public function getApiIdAttribute(){
        return (int) $this->attributes['api_id'];
    }

    protected $hidden = array('pivot');

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

    /**
     * @return boolean
     */
    public function isSystem()
    {
        return $this->system;
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    public function api()
    {
        return $this->belongsTo('Api');
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

    public function getApiLogo(){
        $api = $this->api()->first();
        return !is_null($api) ? $api->getLogo():asset('/assets/apis/server.png');
    }

    /**
     * @return \oauth2\models\IApi
     */
    public function getApi()
    {
        return $this->api();
    }

    /**
     * @return bool
     */
    public function isAssignableByGroups()
    {
         return $this->assigned_by_groups;
    }
}