<?php


namespace oauth2\services;

use oauth2\models\IClient;

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
    public function addClient($client_id, $client_secret,$client_type, $user_id, $app_name, $app_description, $app_logo=null);
    public function addClientScope($id,$scope_id);
    public function deleteClientScope($id,$scope_id);
    public function addClientAllowedUri($id,$uri);
    public function deleteClientAllowedUri($id,$uri);
    public function addClientAllowedRealm($id,$realm);
    public function deleteClientAllowedRealm($id,$realm);
    public function deleteClientByIdentifier($id);
} 