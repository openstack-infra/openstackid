<?php

namespace oauth2\services;

use oauth2\models\IClient;

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
     * @throws InvalidClientException;
     * @return list
     */
    public function getCurrentClientAuthInfo();

    public function getClientByIdentifier($id);

    /**
     * Creates a new client
     * @param $client_type
     * @param $user_id
     * @param $app_name
     * @param $app_description
     * @param string $app_logo
     * @return IClient
     */
    public function addClient($client_type, $user_id, $app_name, $app_description, $app_logo='');
    public function addClientScope($id,$scope_id);
    public function deleteClientScope($id,$scope_id);

    /**
     * Add a new allowed redirection uri
     * @param $id client id
     * @param $uri allowed redirection uri
     * @return mixed
     */
    public function addClientAllowedUri($id,$uri);

    /**
     * Deletes a former client allowed redirection Uri
     * @param $id client identifier
     * @param $uri_id uri identifier
     */
    public function deleteClientAllowedUri($id,$uri_id);

    public function deleteClientByIdentifier($id);

    /**
     * Regenerates Client Secret
     * @param $id client id
     * @return mixed
     */
    public function regenerateClientSecret($id);

    /**
     * Lock a client application by client id
     * @param $client_id client id
     * @return mixed
     */
    public function lockClient($client_id);

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
     * gets an api scope by id
     * @param $id id of api scope
     * @return IApiScope
     */
    public function get($id);

    /**
     * Gets a paginated list of clients
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @return mixed
     */
    public function getAll($page_nbr=1,$page_size=10,array $filters);

    /**
     * @param IClient $client
     * @return bool
     */
    public function save(IClient $client);

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws \oauth2\exceptions\InvalidClientException
     */
    public function update($id, array $params);
} 