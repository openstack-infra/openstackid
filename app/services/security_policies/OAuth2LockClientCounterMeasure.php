<?php

namespace services;

use Exception;
use Log;
use oauth2\services\IClientService;
use utils\services\ISecurityPolicyCounterMeasure;

class OAuth2LockClientCounterMeasure implements ISecurityPolicyCounterMeasure{


	private $client_service;

	public function __construct(IClientService $client_service){
		$this->client_service = $client_service;
	}

    public function trigger(array $params = array())
    {
        try{

            if (!isset($params["client_id"])) return;
            $client_id       = $params['client_id'];
	        $client = $this->client_service->getClientByIdentifier($client_id);
            if(is_null($client))
                return;
            //apply lock policy
	        $this->client_service->lockClient($client->id);
        }
        catch(Exception $ex){
            Log::error($ex);
        }
    }
}