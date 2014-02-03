<?php

namespace services;

use Exception;
use Log;
use oauth2\services\OAuth2ServiceCatalog;
use utils\services\Registry;
use utils\services\ISecurityPolicyCounterMeasure;
use Client as OAuth2Client;

class OAuth2LockClientCounterMeasure implements ISecurityPolicyCounterMeasure{

    public function trigger(array $params = array())
    {
        try{

            if (!isset($params["client_id"])) return;
            $client_id       = $params['client_id'];

            $client_service         = Registry::getInstance()->get(OAuth2ServiceCatalog::ClientService);
            $client = OAuth2Client::where('id', '=', client_id)->first();
            if(is_null($client))
                return;
            //apply lock policy
            $client_service->lockClient($client->id);
        }
        catch(Exception $ex){
            Log::error($ex);
        }
    }
}