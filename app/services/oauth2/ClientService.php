<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/4/13
 * Time: 12:45 PM
 */

namespace services\oauth2;
use oauth2\models\IClient;
use oauth2\services\IClientService;
use Client;

class ClientService implements IClientService{

    /**
     * @param $client_id
     * @return IClient
     */
    public function getClientById($client_id)
    {
        $client = Client::where('client_id', '=', $client_id)->first();
        return $client;
    }
}