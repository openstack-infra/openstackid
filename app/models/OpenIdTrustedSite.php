<?php

use openid\model\ITrustedSite;

class OpenIdTrustedSite extends Eloquent implements ITrustedSite
{

    public $timestamps = false;
    protected $table = 'openid_trusted_sites';

    public function getRealm()
    {
        return $this->realm;
    }

    public function getUITrustedData()
    {
        $data = $this->getData();
        $str = '';
        foreach ($data as $val) {
            $str .= $val . ', ';
        }
        return trim($str, ', ');
    }

    public function getData()
    {
        $res = $this->data;
        return json_decode($res);
    }

    public function getUser()
    {
        return $this->user();
    }

    public function user()
    {
        return $this->belongsTo('auth\OpenIdUser');
    }

    public function getAuthorizationPolicy()
    {
        return $this->policy;
    }

}