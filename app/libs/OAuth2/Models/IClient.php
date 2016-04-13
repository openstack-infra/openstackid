<?php namespace OAuth2\Models;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use Utils\Model\IEntity;
/**
 * Interface IClient
 * @package OAuth2\Models
 */
interface IClient extends IEntity
{

    const ClientType_Public         = 'PUBLIC';
    const ClientType_Confidential   = 'CONFIDENTIAL';

    const ApplicationType_Web_App   = 'WEB_APPLICATION';
    const ApplicationType_JS_Client = 'JS_CLIENT';
    const ApplicationType_Service   = 'SERVICE';
    const ApplicationType_Native    = 'NATIVE';

    /**
     *  @see http://openid.net/specs/openid-connect-core-1_0.html#SubjectIDTypes
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
     * @return string[]
     */
    public function getRedirectUris();

    /**
     * @param $uri
     * @return bool
     */
    public function isUriAllowed($uri);

    /**
     * returns all registered allowed js origins for this client
     * @return string[]
     */
    public function getClientAllowedOrigins();

    /**
     * @param string $origin
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
     * @return string[]
     */
    public function getPostLogoutUris();

    /**
     * @return string
     */
    public function getLogoutUri();

    /**
     * @return JWTResponseInfo
     */
    public function getIdTokenResponseInfo();

    /**
     * @return JWTResponseInfo
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

    /**
     * @return IClientPublicKey[]
     */
    public function getPublicKeys();

    /**
     * @return IClientPublicKey[]
     */
    public function getPublicKeysByUse($use);

    /**
     * @param string $use
     * @param string $alg
     * @return IClientPublicKey
     */
    public function getCurrentPublicKeyByUse($use, $alg);

    /**
     * @param string $kid
     * @return IClientPublicKey
     */
    public function getPublicKeyByIdentifier($kid);

    /**
     * @param IClientPublicKey $public_key
     * @return $this
     */
    public function addPublicKey(IClientPublicKey $public_key);

    /**
     * @return string
     */
    public function getJWKSUri();


    /**
     * @param string $post_logout_uri
     * @return bool
     */
    public function isPostLogoutUriAllowed($post_logout_uri);

    /**
     * @param $user
     */
    public function candEdit($user);

    /**
     * @param $user
     * @return bool
     */
    public function canDelete($user);

    /**
     * @param $user
     * @return $this
     */
    public function setOwner($user);


    /**
     * @return $this
     */
    public function removeAllScopes();

    /**
     * @param IApiScope $scope
     * @return $this
     */
    public function addScope(IApiScope $scope);

    /**
     * @param $editing_user
     * @return $this
     */
    public function setEditedBy($editing_user);

    /**
     * @return bool
     */
    public function useRefreshToken();

    /**
     * @return bool
     */
    public function useRotateRefreshTokenPolicy();

    /**
     * @return array
     */
    public function getValidAccessTokens();
}