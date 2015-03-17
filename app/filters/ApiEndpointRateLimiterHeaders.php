<?php
/**
 * Copyright 2015 Openstack Foundation
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

use oauth2\services\IApiEndpointService;
use utils\services\ILogService;
use utils\services\ICheckPointService;
use utils\services\ICacheService;

/**
 * Class ApiEndpointRateLimiterHeaders
 */
class ApiEndpointRateLimiterHeaders {

    /**
     * @var IApiEndpointService
     */
    private $api_endpoint_service;
    /**
     * @var ILogService
     */
    private $log_service;
    /**
     * @var ICheckPointService
     */
    private $checkpoint_service;
    /**
     * @var ICacheService
     */
    private $cache_service;

    /**
     * @param IApiEndpointService $api_endpoint_service
     * @param ILogService         $log_service
     * @param ICheckPointService  $checkpoint_service
     * @param ICacheService       $cache_service
     */
    public function __construct(IApiEndpointService $api_endpoint_service, ILogService $log_service, ICheckPointService $checkpoint_service, ICacheService $cache_service){
        $this->api_endpoint_service    = $api_endpoint_service;
        $this->log_service             = $log_service;
        $this->checkpoint_service      = $checkpoint_service;
        $this->cache_service           = $cache_service;
    }

    /**
     * @param $route
     * @param $request
     * @param $response
     */
    public function filter($route, $request, $response)
    {
        $url = $route->getPath();
        if(strpos($url, '/') != 0){
            $url =   '/'.$url;
        }
        $method    = $request->getMethod();

        try {
            $endpoint = $this->api_endpoint_service->getApiEndpointByUrlAndMethod($url, $method);
            if(!is_null($endpoint->rate_limit) && (int)$endpoint->rate_limit > 0){
                //do rate limit checking
                $key = sprintf('rate.limit.%s_%s_%s',$url,$method,$request->getClientIp());
                $res = (int)$this->cache_service->getSingleValue($key);
                if($res <= (int)$endpoint->rate_limit)
                {
                    $response->headers->set('X-Ratelimit-Limit', $endpoint->rate_limit, false);
                    $response->headers->set('X-Ratelimit-Remaining', $endpoint->rate_limit-(int)$res, false);
                    $response->headers->set('X-RateLimit-Reset', $this->cache_service->ttl(($key)) , false);
                }
            }
        }
        catch(Exception $ex){
            $this->log_service->error($ex);
            $this->checkpoint_service->trackException($ex);
        }
    }
}