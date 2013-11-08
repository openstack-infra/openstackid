<?php


namespace openid\services;


interface IServerConfigurationService
{
    public function getOPEndpointURL();

    public function getUserIdentityEndpointURL($identifier);

    public function getPrivateAssociationLifetime();

    public function getSessionAssociationLifetime();

    public function getMaxFailedLoginAttempts();

    public function getMaxFailedLoginAttempts2ShowCaptcha();

    public function getNonceLifetime();

    public function getAssetsUrl($asset_path);
}