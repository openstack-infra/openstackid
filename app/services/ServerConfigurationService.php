<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/18/13
 * Time: 12:30 PM
 * To change this template use File | Settings | File Templates.
 */

namespace services;

use openid\services\IServerConfigurationService;
use \BannedIP;
use \ServerConfiguration;

class ServerConfigurationService implements IServerConfigurationService{

    private $private_association_lifetime;
    private $session_association_lifetime;
    private $max_failed_login_attempts;
    private $max_failed_login_attempts_2_show_captcha;
    private $nonce_lifetime;
    private $assets_url;

    public function __construct(){
        $this->private_association_lifetime             = null;
        $this->session_association_lifetime             = null;
        $this->max_failed_login_attempts                = null;
        $this->max_failed_login_attempts_2_show_captcha = null;
        $this->nonce_lifetime                           = null;
        $this->assets_url                               = null;
    }

    public function getUserIdentityEndpointURL($identifier){
        $url = action("UserController@getIdentity",array("identifier"=>$identifier));
        return $url;
    }

    public function getOPEndpointURL()
    {
        $url = action("OpenIdProviderController@op_endpoint");
        return $url;
    }

    public function getPrivateAssociationLifetime()
    {
        if(is_null($this->private_association_lifetime)){
            $conf = ServerConfiguration::where('key','=','Private.Association.Lifetime')->first();
            if(!$conf || !is_numeric($conf->value)) $this->private_association_lifetime = 120;
            else $this->private_association_lifetime = intval($conf->value);
        }
        return $this->private_association_lifetime;
    }

    public function getSessionAssociationLifetime()
    {
        if(is_null($this->session_association_lifetime)){
            $conf = ServerConfiguration::where('key','=','Session.Association.Lifetime')->first();
            if(!$conf || !is_numeric($conf->value)) $this->session_association_lifetime = 3600*6;
            else $this->session_association_lifetime= intval($conf->value);
        }
        return $this->session_association_lifetime;
    }

    public function getMaxFailedLoginAttempts(){
        if(is_null($this->max_failed_login_attempts )){
            $conf = ServerConfiguration::where('key','=','MaxFailed.Login.Attempts')->first();
            if(!$conf || !is_numeric($conf->value)) $this->max_failed_login_attempts= 10;
            else $this->max_failed_login_attempts= intval($conf->value);
        }
        return $this->max_failed_login_attempts;
    }

    public function getMaxFailedLoginAttempts2ShowCaptcha(){
        if(is_null($this->max_failed_login_attempts_2_show_captcha)){
            $conf = ServerConfiguration::where('key','=','MaxFailed.LoginAttempts.2ShowCaptcha')->first();
            if(!$conf || !is_numeric($conf->value)) $this->max_failed_login_attempts_2_show_captcha = 3;
            else $this->max_failed_login_attempts_2_show_captcha = intval($conf->value);
        }
        return $this->max_failed_login_attempts_2_show_captcha;
    }

    public function getNonceLifetime(){
        if(is_null($this->nonce_lifetime)){
            $conf = ServerConfiguration::where('key','=','Nonce.Lifetime')->first();
            if(!$conf || !is_numeric($conf->value)) $this->nonce_lifetime = 360;
            else $this->nonce_lifetime = intval($conf->value);
        }
        return $this->nonce_lifetime;
    }

    public function getAssetsUrl($asset_path){
        if(is_null($this->assets_url)){
            $conf = ServerConfiguration::where('key','=','Assets.Url')->first();
            if(!$conf) $this->assets_url =  '';
            else $this->assets_url = $conf->value;
        }
        return $this->assets_url.$asset_path;
    }

    public function isValidIP($remote_address){
        $res = true;
        $banned_ip = BannedIP::where("ip","=",$remote_address)->first();
        if($banned_ip){
            $banned_ip->hits = $banned_ip->hits + 1;
            $banned_ip->Save();
            sleep(2 ^ $banned_ip->hits);
            $res = false;
        }
        return $res;
    }
}