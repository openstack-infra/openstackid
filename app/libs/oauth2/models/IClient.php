<?php

namespace oauth2\models;

/**
 * Interface IClient
 * @package oauth2\models
 */
interface IClient {

    const ClientType_Public         = 'PUBLIC';
    const ClientType_Confidential   = 'CONFIDENTIAL';

    const ApplicationType_Web_App   = 'WEB_APPLICATION';
    const ApplicationType_JS_Client = 'JS_CLIENT';
    const ApplicationType_Service   = 'SERVICE';

    public function getId();
    public function getClientId();
    public function getClientSecret();
    public function getClientType();
    public function getApplicationType();

    public function getClientScopes();
    public function isScopeAllowed($scope);

    public function getClientRegisteredUris();
    public function isUriAllowed($uri);

    public function getClientAllowedOrigins();
    public function isOriginAllowed($origin);

    public function getApplicationName();
    public function getApplicationLogo();
    public function getApplicationDescription();
    public function getDeveloperEmail();
    public function getUserId();
    public function isLocked();
    public function isActive();
    public function isResourceServerClient();
    public function getResourceServer();
    public function getFriendlyApplicationType();
}