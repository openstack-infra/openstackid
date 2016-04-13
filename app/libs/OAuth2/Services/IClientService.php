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
use OAuth2\Exceptions\AbsentClientException;
use OAuth2\Exceptions\InvalidClientAuthMethodException;
use OAuth2\Exceptions\MissingClientAuthorizationInfo;
use OAuth2\Models\ClientAuthenticationContext;
use OAuth2\Models\IClient;
use Services\Exceptions\ValidationException;
/**
 * Interface IClientService
 * @package OAuth2\Services
 */
interface IClientService {
    /**
     * @param $client_id
     * @return IClient
     */
    public function getClientById($client_id);

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
     * @param int $id
     * @return IClient
     */
    public function getClientByIdentifier($id);

    /**
     * @param string $application_type
     * @param string $app_name
     * @param string $app_description
     * @param null|string  $app_url
     * @param array $admin_users
     * @param string $app_logo
     * @return IClient
     */
    public function addClient
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
     * @throws AbsentClientException
     * @throws ValidationException
     * @return mixed
     */
    public function update($id, array $params);

    /**
     * @param $id
     * @param $scope_id
     * @return mixed
     */
    public function addClientScope($id, $scope_id);

    /**
     * @param $id
     * @param $scope_id
     * @return mixed
     */
    public function deleteClientScope($id, $scope_id);


    public function deleteClientByIdentifier($id);

    /**
     * Regenerates Client Secret
     * @param int $id
     * @return IClient
     */
    public function regenerateClientSecret($id);

    /**
     * Lock a client application by client id
     * @param string $client_id
     * @return mixed
     */
    public function lockClient($client_id);

    /**
     * unLock a client application by client id
     * @param string $client_id
     * @return mixed
     */
    public function unlockClient($client_id);

    /**
     * Activate/Deactivate given client
     * @param int $id
     * @param $active
     * @return mixed
     */
    public function activateClient($id, $active);

    /**
     * set/unset refresh token usage for a given client
     * @param $id
     * @param $use_refresh_token
     * @return mixed
     */
    public function setRefreshTokenUsage($id, $use_refresh_token);

    /**
     * set/unset rotate refresh token policy for a given client
     * @param $id
     * @param $rotate_refresh_token
     * @return mixed
     */
    public function setRotateRefreshTokenPolicy($id, $rotate_refresh_token);

    /**
     * Checks if an app name already exists or not
     * @param $app_name
     * @return boolean
     */
    public function existClientAppName($app_name);

    /**
     * gets a client by id
     * @param $id
     * @return IClient
     */
    public function get($id);

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr=1, $page_size=10, array $filters=array(), array $fields=array('*'));

    /**
     * @param string $origin
     * @return IClient
     */
    public function getByOrigin($origin);

} 