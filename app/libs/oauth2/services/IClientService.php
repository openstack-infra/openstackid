<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/3/13
 * Time: 4:39 PM
 */

namespace oauth2\services;

use oauth2\models\IClient;

interface IClientService {
    /**
     * @param $client_id
     * @return IClient
     */
    public function getClientById($client_id);
} 