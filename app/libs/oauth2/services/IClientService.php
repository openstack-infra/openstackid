<?php

namespace oauth2\services;

use oauth2\exceptions\AbsentClientException;
use oauth2\exceptions\InvalidClientAuthMethodException;
use oauth2\exceptions\MissingClientAuthorizationInfo;
use oauth2\models\ClientAuthenticationContext;
use oauth2\models\IClient;
use openid\model\IOpenIdUser;
/**
 * Interface IClientService
 * @package oauth2\services
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
     * implementation of http://tools.ietf.org/html/rfc6749#section-2.3.1
     * implementation of http://openid.net/specs/openid-connect-core-1_0.html#ClientAuthentication
     * @throws InvalidClientAuthMethodException
     * @throws MissingClientAuthorizationInfo
     * @return ClientAuthenticationContext
     */
    public function getCurrentClientAuthInfo();

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
     * @throws \ValidationException
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
     * @param $id client id
     * @return IClient
     */
    public function regenerateClientSecret($id);

    /**
     * Lock a client application by client id
     * @param $client_id client id
     * @return mixed
     */
    public function lockClient($client_id);

    /**
     * unLock a client application by client id
     * @param $client_id client id
     * @return mixed
     */
    public function unlockClient($client_id);

    /**
     * Activate/Deactivate given client
     * @param $id
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
     * @param $id id of client
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
    public function getAll($page_nbr=1,$page_size=10,array $filters=array(), array $fields=array('*'));

    /**
     * @param string $origin
     * @return IClient
     */
    public function getByOrigin($origin);

} 