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

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use OAuth2\Exceptions\OAuth2ClientBaseException;
use OAuth2\Repositories\IClientRepository;
use OAuth2\Services\IClientService;
use Utils\Services\ISecurityPolicy;
use Utils\Services\ISecurityPolicyCounterMeasure;
use Models\OAuth2\OAuth2TrailException;
use Utils\Services\IServerConfigurationService;
use Utils\IPHelper;

/**
 * Class OAuth2SecurityPolicy
 * @package Services\SecurityPolicies
 */
class OAuth2SecurityPolicy  implements ISecurityPolicy {

    /**
     * @var ISecurityPolicyCounterMeasure
     */
    protected $counter_measure;
    /**
     * @var array
     */
    private $exception_dictionary = array();
    /**
     * @var IServerConfigurationService
     */
    private $server_configuration_service;
    /**
     * @var IClientRepository
     */
    private $client_repository;

    /**
     * OAuth2SecurityPolicy constructor.
     * @param IServerConfigurationService $server_configuration_service
     * @param IClientRepository $client_repository
     */
    public function __construct(IServerConfigurationService $server_configuration_service, IClientRepository $client_repository)
    {
        $this->server_configuration_service = $server_configuration_service;
	    $this->client_repository            = $client_repository;

        $this->exception_dictionary = array(
            'auth2\exceptions\BearerTokenDisclosureAttemptException' => array('OAuth2SecurityPolicy.MaxBearerTokenDisclosureAttempts'),
            'auth2\exceptions\InvalidClientException'                => array('OAuth2SecurityPolicy.MaxInvalidClientExceptionAttempts'),
            'auth2\exceptions\InvalidRedeemAuthCodeException'        => array('OAuth2SecurityPolicy.MaxInvalidRedeemAuthCodeAttempts'),
            'auth2\exceptions\InvalidClientCredentials'              => array('OAuth2SecurityPolicy.MaxInvalidClientCredentialsAttempts'),
        );
    }
    /**
     * Check if current security policy is meet or not
     * @return boolean
     */
    public function check()
    {
        return true;
    }

    /**
     * Apply security policy on a exception
     * @param Exception $ex
     * @return $this
     */
    public function apply(Exception $ex)
    {
        try {
            if($ex instanceof OAuth2ClientBaseException){
                $client_id = $ex->getClientId();
                //save oauth2 exception by client id
                if (!is_null($client_id) && !empty($client_id)){
                    $client                = $this->client_repository->getClientById($client_id);
                    if(!is_null($client)) {
                        $exception_class       = get_class($ex);
                        $trail                 = new OAuth2TrailException();
                        $trail->from_ip        = IPHelper::getUserIp();
                        $trail->exception_type = $exception_class;
                        $trail->client_id      = $client->getId();
                        $trail->Save();

                        //check exception count by type on last "MinutesWithoutExceptions" minutes...
                        $exception_count = intval(OAuth2TrailException::where('client_id', '=', intval($client->getId()))
                            ->where('exception_type', '=', $exception_class)
                            ->where('created_at', '>', DB::raw('( UTC_TIMESTAMP() - INTERVAL ' . $this->server_configuration_service->getConfigValue("OAuth2SecurityPolicy.MinutesWithoutExceptions") . ' MINUTE )'))
                            ->count());

                        if(array_key_exists($exception_class,$this->exception_dictionary)){
                            $params                   = $this->exception_dictionary[$exception_class];
                            $max_attempts             = !is_null($params[0]) && !empty($params[0])? intval($this->server_configuration_service->getConfigValue($params[0])):0;
                            if ($exception_count >= $max_attempts)
                                $this->counter_measure->trigger(array('client_id' => $client->getId()));
                        }
                    }
                }
            }

        } catch (Exception $ex) {
            Log::error($ex);
        }
        return $this;
    }

    /**
     * @param ISecurityPolicyCounterMeasure $counter_measure
     * @return $this
     */
    public function setCounterMeasure(ISecurityPolicyCounterMeasure $counter_measure)
    {
        $this->counter_measure = $counter_measure;
        return $this;
    }
}