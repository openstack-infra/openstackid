<?php

namespace oauth2\models;

/**
 * Interface IClient
 * @package oauth2\models
 */
interface IClient
{

    const ClientType_Public         = 'PUBLIC';
    const ClientType_Confidential   = 'CONFIDENTIAL';

    const ApplicationType_Web_App   = 'WEB_APPLICATION';
    const ApplicationType_JS_Client = 'JS_CLIENT';
    const ApplicationType_Service   = 'SERVICE';
    const ApplicationType_Native    = 'NATIVE';

    /**
     *  http://openid.net/specs/openid-connect-core-1_0.html#SubjectIDTypes
     */
    const SubjectType_Public   = 'public';
    const SubjectType_Pairwise = 'pairwise';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getClientId();

    /**
     * @return null|string
     */
    public function getClientSecret();

    /**
     * @return string
     */
    public function getClientType();

    /**
     * @return string
     */
    public function getApplicationType();

    /**
     * @return mixed
     */
    public function getClientScopes();

    /**
     * @param $scope
     * @return bool
     */
    public function isScopeAllowed($scope);

    /**
     * @return mixed
     */
    public function getClientRegisteredUris();

    /**
     * @param $uri
     * @return bool
     */
    public function isUriAllowed($uri);

    /**
     * returns all registered allowed js origins for this client
     * @return mixed
     */
    public function getClientAllowedOrigins();

    /**
     * @param $origin
     * @return bool
     */
    public function isOriginAllowed($origin);

    /**
     * gets application name
     * @return string
     */
    public function getApplicationName();

    /** gets application log url
     * @return string
     */
    public function getApplicationLogo();

    /**
     * gets application description
     * @return string
     */
    public function getApplicationDescription();

    /**
     * gets application developer email
     * @return string
     */
    public function getDeveloperEmail();

    /**
     * gets user id that owns this application
     * @return int
     */
    public function getUserId();

    /**
     * @return bool
     */
    public function isLocked();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * clients could be associated to resource server in order
     * to do server to server communication
     * @return bool
     */
    public function isResourceServerClient();

    /**
     * gets associated resource server
     * @return null|IResourceServer
     */
    public function getResourceServer();

    /**
     * @return string
     */
    public function getFriendlyApplicationType();

    /**
     * gets application website url
     * @return string
     */
    public function getWebsite();

    /**
     * @return \DateTime
     */
    public function getClientSecretExpiration();

    /**
     * @return bool
     */
    public function isClientSecretExpired();

    /**
     * @return string[]
     */
    public function getContacts();

    /**
     * @return int
     */
    public function getDefaultMaxAge();

    /**
     * @return bool
     */
    public function requireAuthTimeClaim();

    /**
     * @return string
     */
    public function getLogoUri();

    /**
     * @return string
     */
    public function getPolicyUri();

    /**
     * @return string
     */
    public function getTermOfServiceUri();

    /**
     * @return string
     */
    public function getPostLogoutUri();

    /**
     * @return string
     */
    public function getLogoutUri();

    /**
     * @return IdTokenResponseInfo
     */
    public function getIdTokenResponseInfo();

    /**
     * @return UserInfoResponseInfo
     */
    public function getUserInfoResponseInfo();

    /**
     * @return TokenEndpointAuthInfo
     */
    public function getTokenEndpointAuthInfo();

    /**
     * @return string
     */
    public function getSubjectType();
}