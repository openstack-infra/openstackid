<?php namespace Services\SecurityPolicies;
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

use Exception;
use Illuminate\Support\Facades\Log;
use OAuth2\Services\IClientService;
use Utils\Services\ISecurityPolicyCounterMeasure;

/**
 * Class OAuth2LockClientCounterMeasure
 * @package Services\SecurityPolicies
 */
class OAuth2LockClientCounterMeasure implements ISecurityPolicyCounterMeasure{

    /**
     * @var IClientService
     */
	private $client_service;

    /**
     * OAuth2LockClientCounterMeasure constructor.
     * @param IClientService $client_service
     */
	public function __construct(IClientService $client_service){
		$this->client_service = $client_service;
	}

    /**
     * @param array $params
     * @return $this
     */
    public function trigger(array $params = array())
    {
        try{

            if (isset($params["client_id"])) {
                $client_id = $params['client_id'];
                $client    = $this->client_service->getClientByIdentifier($client_id);
                if (!is_null($client))
                    $this->client_service->lockClient($client->id);
            }
        }
        catch(Exception $ex){
            Log::error($ex);
        }
        return $this;
    }
}