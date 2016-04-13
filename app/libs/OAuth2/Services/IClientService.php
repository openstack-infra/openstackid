<?php namespace OAuth2\Services;
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


use OAuth2\Exceptions\InvalidClientAuthMethodException;
use OAuth2\Exceptions\MissingClientAuthorizationInfo;
use OAuth2\Models\ClientAuthenticationContext;
use OAuth2\Models\IClient;
use Services\Exceptions\ValidationException;
use Utils\Exceptions\EntityNotFoundException;

/**
 * Interface IClientService
 * @package OAuth2\Services
 */
interface IClientService {

    /**
     * Clients in possession of a client password MAY use the HTTP Basic
     * authentication scheme as defined in [RFC2617] to authenticate with
     * the authorization server
     * Alternatively, the authorization server MAY support including the
     * client credentials in the request-body using the following
     * parameters:
     * implementation of @link http://tools.ietf.org/html/rfc6749#section-2.3.1
     * implementation of @link http://openid.net/specs/openid-connect-core-1_0.html#ClientAuthentication
     * @throws InvalidClientAuthMethodException
     * @throws MissingClientAuthorizationInfo
     * @return ClientAuthenticationContext
     */
    public function getCurrentClientAuthInfo();

    /**
     * @param string $application_type
     * @param string $app_name
     * @param string $app_description
     * @param null|string  $app_url
     * @param array $admin_users
     * @param string $app_logo
     * @return IClient
     */
    public function register
    (
        $application_type,
        $app_name,
        $app_description,
        $app_url = null,
        array $admin_users = array(),
        $app_logo = ''
    );

    /**
     * @param $id
     * @param array $params
     * @throws EntityNotFoundException
     * @throws ValidationException
     * @return mixed
     */
    public function update($id, array $params);

    /**
     * @param int $id
     * @param int $scope_id
     * @return mixed
     */
    public function addClientScope($id, $scope_id);

    /**
     * @param int $id
     * @param int $scope_id
     * @return mixed
     */
    public function deleteClientScope($id, $scope_id);

    /**
     * @param  int $id
     * @return mixed
     */
    public function deleteClientByIdentifier($id);

    /**
     * Regenerates Client Secret
     * @param int $id
     * @return IClient
     */
    public function regenerateClientSecret($id);

    /**
     * Lock a client application by id
     * @param int $id
     * @return bool
     */
    public function lockClient($id);

    /**
     * unLock a client application by id
     * @param string $id
     * @return bool
     */
    public function unlockClient($id);

    /**
     * Activate/Deactivate given client
     * @param int $id
     * @param bool $active
     * @return bool
     */
    public function activateClient($id, $active);

    /**
     * set/unset refresh token usage for a given client
     * @param int $id
     * @param bool $use_refresh_token
     * @return bool
     */
    public function setRefreshTokenUsage($id, $use_refresh_token);

    /**
     * set/unset rotate refresh token policy for a given client
     * @param int $id
     * @param bool $rotate_refresh_token
     * @return bool
     */
    public function setRotateRefreshTokenPolicy($id, $rotate_refresh_token);

    /**
     * Checks if an app name already exists or not
     * @param string $app_name
     * @return bool
     */
    public function existClientAppName($app_name);

} 