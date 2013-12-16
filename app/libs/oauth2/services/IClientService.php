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
     * @return list
     */
    public function getCurrentClientAuthInfo();

    public function getClientByIdentifier($id);
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

    public function addClientAllowedRealm($id,$realm);
    public function deleteClientAllowedRealm($id,$realm_id);
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


    public function activateClient($id, $active,$user_id);
} 