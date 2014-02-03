<?php

namespace services;

use DB;
use Exception;
use Log;
use utils\services\ISecurityPolicy;
use utils\services\ISecurityPolicyCounterMeasure;
use OAuth2TrailException;
use utils\services\IServerConfigurationService;

/**
 * Class OAuth2SecurityPolicy
 * @package services
 */
class OAuth2SecurityPolicy  implements ISecurityPolicy{


    private $exception_dictionary = array();
    private $server_configuration_service;

    public function __construct(IServerConfigurationService $server_configuration_service)
    {
        $this->server_configuration_service = $server_configuration_service;

        $this->exception_dictionary = array(
            'auth2\exceptions\BearerTokenDisclosureAttemptException' => array('OAuth2SecurityPolicy.MaxBearerTokenDisclosureAttempts'),
            'auth2\exceptions\InvalidClientException' => array('OAuth2SecurityPolicy.MaxInvalidClientExceptionAttempts'),
            'auth2\exceptions\InvalidRedeemAuthCodeException' => array('OAuth2SecurityPolicy.MaxInvalidRedeemAuthCodeAttempts'),
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
     * @return mixed
     */
    public function apply(Exception $ex)
    {
        try {
            if(is_subclass_of($ex,'oauth2\\exceptions\\OAuth2ClientBaseException')){
                $client_id = $ex->getClientId();
                //save oauth2 exception by client id
                if (!is_null($client_id) && !empty($client_id)){
                    $exception_class       = get_class($ex);
                    $trail                 = new OAuth2TrailException();
                    $trail->from_ip        = IPHelper::getUserIp();
                    $trail->exception_type = $exception_class;
                    $trail->client_id      = $client_id;
                    $trail->Save();


                    //check exception count by type on last "MinutesWithoutExceptions" minutes...
                    $exception_count = intval(OAuth2TrailException::where('client_id', '=', intval($client_id))
                        ->where('exception_type', '=', $exception_class)
                        ->where('created_at', '>', DB::raw('( UTC_TIMESTAMP() - INTERVAL ' . $this->server_configuration_service->getConfigValue("OAuth2SecurityPolicy.MinutesWithoutExceptions") . ' MINUTE )'))
                        ->count());

                    if(array_key_exists($exception_class,$this->exception_dictionary)){
                        $params                   = $this->exception_dictionary[$exception_class];
                        $max_attempts             = !is_null($params[0]) && !empty($params[0])? intval($this->server_configuration_service->getConfigValue($params[0])):0;
                        if ($exception_count >= $max_attempts)
                            $this->counter_measure->trigger(array('client_id' => $client_id));
                    }


                }
            }

        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    public function setCounterMeasure(ISecurityPolicyCounterMeasure $counter_measure)
    {
        $this->counter_measure = $counter_measure;
    }
}