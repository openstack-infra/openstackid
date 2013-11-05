<?php

namespace services;

use BannedIP;
use Exception;
use openid\services\IServerConfigurationService;
use ServerConfiguration;

class ServerConfigurationService implements IServerConfigurationService
{

    const DefaultAssetsUrl = 'http://www.openstack.org/';
    const DefaultPrivateAssociationLifetime = 120;
    const DefaultSessionAssociationLifetime = 21600;
    const DefaultMaxFailedLoginAttempts = 10;
    const DefaultMaxFailedLoginAttempts2ShowCaptcha = 3;
    const DefaultNonceLifetime = 360;
    private $private_association_lifetime;
    private $session_association_lifetime;
    private $max_failed_login_attempts;
    private $max_failed_login_attempts_2_show_captcha;
    private $nonce_lifetime;
    private $assets_url;

    public function __construct()
    {
        $this->private_association_lifetime = null;
        $this->session_association_lifetime = null;
        $this->max_failed_login_attempts = null;
        $this->max_failed_login_attempts_2_show_captcha = null;
        $this->nonce_lifetime = null;
        $this->assets_url = null;
    }

    public function getUserIdentityEndpointURL($identifier)
    {
        $url = action("UserController@getIdentity", array("identifier" => $identifier));
        return $url;
    }

    public function getOPEndpointURL()
    {
        $url = action("OpenIdProviderController@op_endpoint");
        return $url;
    }

    public function getPrivateAssociationLifetime()
    {
        try {
            if (is_null($this->private_association_lifetime)) {
                $conf = ServerConfiguration::where('key', '=', 'Private.Association.Lifetime')->first();
                if (!$conf || !is_numeric($conf->value)) $this->private_association_lifetime = self::DefaultPrivateAssociationLifetime;
                else $this->private_association_lifetime = intval($conf->value);
            }
            return $this->private_association_lifetime;
        } catch (Exception $ex) {
            Log::error($ex);
            return self::DefaultPrivateAssociationLifetime;
        }
    }

    public function getSessionAssociationLifetime()
    {
        try {
            if (is_null($this->session_association_lifetime)) {
                $conf = ServerConfiguration::where('key', '=', 'Session.Association.Lifetime')->first();
                if (!$conf || !is_numeric($conf->value)) $this->session_association_lifetime = self::DefaultSessionAssociationLifetime;
                else $this->session_association_lifetime = intval($conf->value);
            }
            return $this->session_association_lifetime;
        } catch (Exception $ex) {
            Log::error($ex);
            return self::DefaultSessionAssociationLifetime;
        }
    }

    public function getMaxFailedLoginAttempts()
    {
        try {
            if (is_null($this->max_failed_login_attempts)) {
                $conf = ServerConfiguration::where('key', '=', 'MaxFailed.Login.Attempts')->first();
                if (!$conf || !is_numeric($conf->value)) $this->max_failed_login_attempts = self::DefaultMaxFailedLoginAttempts;
                else $this->max_failed_login_attempts = intval($conf->value);
            }
            return $this->max_failed_login_attempts;
        } catch (Exception $ex) {
            Log::error($ex);
            return self::DefaultMaxFailedLoginAttempts;
        }
    }

    public function getMaxFailedLoginAttempts2ShowCaptcha()
    {
        try {
            if (is_null($this->max_failed_login_attempts_2_show_captcha)) {
                $conf = ServerConfiguration::where('key', '=', 'MaxFailed.LoginAttempts.2ShowCaptcha')->first();
                if (!$conf || !is_numeric($conf->value)) $this->max_failed_login_attempts_2_show_captcha = self::DefaultMaxFailedLoginAttempts2ShowCaptcha;
                else $this->max_failed_login_attempts_2_show_captcha = intval($conf->value);
            }
            return $this->max_failed_login_attempts_2_show_captcha;
        } catch (Exception $ex) {
            Log::error($ex);
            return self::DefaultMaxFailedLoginAttempts2ShowCaptcha;
        }
    }

    public function getNonceLifetime()
    {
        try {
            if (is_null($this->nonce_lifetime)) {
                $conf = ServerConfiguration::where('key', '=', 'Nonce.Lifetime')->first();
                if (!$conf || !is_numeric($conf->value)) $this->nonce_lifetime = self::DefaultNonceLifetime;
                else $this->nonce_lifetime = intval($conf->value);
            }
            return $this->nonce_lifetime;
        } catch (Exception $ex) {
            Log::error($ex);
            return self::DefaultNonceLifetime;
        }
    }

    public function getAssetsUrl($asset_path)
    {
        try {
            if (is_null($this->assets_url)) {
                $conf = ServerConfiguration::where('key', '=', 'Assets.Url')->first();
                if (!$conf) $this->assets_url = self::DefaultAssetsUrl;
                else $this->assets_url = $conf->value;
            }
            return $this->assets_url . $asset_path;
        } catch (Exception $ex) {
            Log::error($ex);
            return self::DefaultAssetsUrl . $asset_path;
        }
    }

    public function isValidIP($remote_address)
    {
        $res = true;
        try {
            $banned_ip = BannedIP::where("ip", "=", $remote_address)->first();
            if ($banned_ip) {
                $banned_ip->hits = $banned_ip->hits + 1;
                $banned_ip->Save();
                sleep(2 ^ $banned_ip->hits);
                $res = false;
            }
        } catch (Exception $ex) {
            Log::error($ex);
            $res = false;
        }
        return $res;
    }
}