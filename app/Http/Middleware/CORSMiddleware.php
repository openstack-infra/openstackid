<?php namespace App\Http\Middleware;

/**
 * Copyright 2015 OpenStack Foundation
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
use Closure;
use Utils\Services\ICacheService;
use OAuth2\Models\IApiEndpoint;
use OAuth2\Repositories\IApiEndpointRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Routing\Route;

/**
 *
 * @package App\Http\Middleware\
 * Implementation of http://www.w3.org/TR/cors/
 */
final class CORSMiddleware
{

    const CORS_IP_BLACKLIST_PREFIX = 'CORS_IP_BLACKLIST_PREFIX:';

    private $headers = array();

    /**
     * A header is said to be a simple header if the header field name is an ASCII case-insensitive match for Accept,
     * Accept-Language, or Content-Language or if it is an ASCII case-insensitive match for Content-Type and the header
     * field value media type (excluding parameters) is an ASCII case-insensitive match for
     * application/x-www-form-urlencoded, multipart/form-data, or text/plain.
     */

    protected static $simple_headers = array
    (
        'accept',
        'accept-language',
        'content-language',
        'origin',
    );

    protected static $simple_content_header_values = array(
        'application/x-www-form-urlencode',
        'multipart/form-data',
        'text/plain');

    /**
     * A method is said to be a simple method if it is a case-sensitive match for one of the following:
     * - GET
     * - HEAD
     * - POST
     */
    protected static $simple_http_methods = array('GET', 'HEAD', 'POST');

    const DefaultAllowedHeaders = 'origin, content-type, accept, authorization, x-requested-with';
    const DefaultAllowedMethods = 'GET, POST, OPTIONS, PUT, DELETE';


    /**
     * @var IApiEndpointRepository
     */
    private $endpoint_repository;

    /**
     * @var IApiEndpoint;
     */
    private $current_endpoint = null;


    private $allowed_headers;
    private $allowed_methods;

    /**
     * @var ICacheService
     */
    private $cache_service;

    public function __construct(IApiEndpointRepository $endpoint_repository, ICacheService $cache_service)
    {
        $this->endpoint_repository = $endpoint_repository;
        $this->cache_service       = $cache_service;
        $this->allowed_headers     = Config::get('cors.allowed_headers', self::DefaultAllowedHeaders);
        $this->allowed_methods     = Config::get('cors.allowed_methods', self::DefaultAllowedMethods);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($response = $this->preProcess($request)) {
            return $response;
        }
        //normal processing
        $response = $next($request);
        $this->postProcess($request, $response);
        return $response;
    }

    private function generatePreflightCacheKey($request)
    {
        $cache_id = 'pre-flight-' . $request->getClientIp() . '-' . $request->getRequestUri() . '-' . $request->getMethod();
        return $cache_id;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function preProcess(Request $request)
    {
        $actual_request = false;
        if ($this->isValidCORSRequest($request)) {
            if (!$this->testOriginHeaderScrutiny($request)) {
                $response = new Response();
                $response->setStatusCode(403);
                return $response;
            }
            /* Step 01 : Determine the type of the incoming request */
            $type = $this->getRequestType($request);
            /* Step 02 : Process request according to is type */
            switch ($type) {
                case CORSRequestPreflightType::REQUEST_FOR_PREFLIGHT: {
                    // HTTP request send by client to preflight a further 'Complex' request
                    // sets the original method on request in order to be able to find the
                    // correct route
                    $real_method = $request->headers->get('Access-Control-Request-Method');
                    $route_path  = Route::current()->getPath();

                    $request->setMethod($real_method);

                    if (!$route_path || !$this->checkEndPoint($route_path, $real_method)) {
                        $response = new Response();
                        $response->setStatusCode(403);
                        return $response;
                    }
                    // ----Step 2b: Store pre-flight request data in the Cache to keep (mark) the request as correctly followed the request pre-flight process
                    $data = new CORSRequestPreflightData($request, $this->current_endpoint->supportCredentials());
                    $cache_id = $this->generatePreflightCacheKey($request);
                    $this->cache_service->storeHash($cache_id, $data->toArray(), CORSRequestPreflightData::$cache_lifetime);
                    // ----Step 2c: Return corresponding response - This part should be customized with application specific constraints.....
                    return $this->makePreflightResponse($request);
                }
                    break;
                case CORSRequestPreflightType::COMPLEX_REQUEST: {
                    $cache_id = $this->generatePreflightCacheKey($request);;                    // ----Step 2a: Check if the current request has an entry into the preflighted requests Cache
                    $data = $this->cache_service->getHash($cache_id, CORSRequestPreflightData::$cache_attributes);
                    if (!count($data)) {
                        $response = new Response();
                        $response->setStatusCode(403);
                        return $response;
                    }
                    // ----Step 2b: Check that pre-flight information declared during the pre-flight request match the current request on key information
                    $match = false;
                    // ------Start with comparison of "Origin" HTTP header (according to utility method impl. used to retrieve header reference cannot be null)...
                    if ($request->headers->get('Origin') === $data['origin']) {
                        // ------Continue with HTTP method...
                        if ($request->getMethod() === $data['expected_method']) {
                            // ------Finish with custom HTTP headers (use an method to avoid manual iteration on collection to increase the speed)...
                            $x_headers = self::getCustomHeaders($request);
                            $x_headers_pre = explode(',', $data['expected_custom_headers']);
                            sort($x_headers);
                            sort($x_headers_pre);
                            if (count(array_diff($x_headers, $x_headers_pre)) === 0) {
                                $match = true;
                            }
                        }
                    }
                    if (!$match) {
                        $response = new Response();
                        $response->setStatusCode(403);
                        return $response;
                    }
                    $actual_request = true;
                }
                    break;
                case CORSRequestPreflightType::SIMPLE_REQUEST: {
                    // origins, do not set any additional headers and terminate this set of steps.
                    if (!$this->isAllowedOrigin($request)) {
                        $response = new Response();
                        $response->setStatusCode(403);

                        return $response;
                    }
                    $actual_request = true;
                    // If the resource supports credentials add a single Access-Control-Allow-Origin header, with the value
                    // of the Origin header as value, and add a single Access-Control-Allow-Credentials header with the
                    // case-sensitive string "true" as value.
                    // Otherwise, add a single Access-Control-Allow-Origin header, with either the value of the Origin header
                    // or the string "*" as value.
                }
                    break;
            }
        }
        if ($actual_request) {
            // Save response headers
            $cache_id = $this->generatePreflightCacheKey($request);
            // ----Step 2a: Check if the current request has an entry into the preflighted requests Cache
            $data = $this->cache_service->getHash($cache_id, CORSRequestPreflightData::$cache_attributes);
            $this->headers['Access-Control-Allow-Origin'] = $request->headers->get('Origin');
            if ((bool)$data['allows_credentials']) {
                $this->headers['Access-Control-Allow-Credentials'] = 'true';
            }
            /**
             * During a CORS request, the getResponseHeader() method can only access simple response headers.
             * Simple response headers are defined as follows:
             ** Cache-Control
             ** Content-Language
             ** Content-Type
             ** Expires
             ** Last-Modified
             ** Pragma
             * If you want clients to be able to access other headers,
             * you have to use the Access-Control-Expose-Headers header.
             * The value of this header is a comma-delimited list of response headers you want to expose
             * to the client.
             */
            $exposed_headers = Config::get('cors.exposed_headers', 'Content-Type, Expires');
            if (!empty($exposed_headers)) {
                $this->headers['Access-Control-Expose-Headers'] = $exposed_headers;
            }
        }
    }

    public function postProcess(Request $request, Response $response)
    {
        // add CORS response headers
        if (count($this->headers) > 0) {
            $response->headers->add($this->headers);
        }
        return $response;
    }

    /**
     * @param Request $request
     * @return Response
     */
    private function makePreflightResponse(Request $request)
    {
        $response = new Response();
        if (!$this->isAllowedOrigin($request)) {
            $response->headers->set('Access-Control-Allow-Origin', 'null');
            $response->setStatusCode(403);
            return $response;
        }
        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin'));
        // The Access-Control-Request-Method header indicates which method will be used in the actual
        // request as part of the preflight request
        // check request method
        if ($request->headers->get('Access-Control-Request-Method') != $this->current_endpoint->getHttpMethod()) {
            $response->setStatusCode(405);
            return $response;
        }
        // The Access-Control-Allow-Credentials header indicates whether the response to request
        // can be exposed when the omit credentials flag is unset. When part of the response to a preflight request
        // it indicates that the actual request can include user credentials.
        if ($this->current_endpoint->supportCredentials()) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
        if (Config::get('cors.use_pre_flight_caching', false)) {
            // The Access-Control-Max-Age header indicates how long the response can be cached, so that for
            // subsequent requests, within the specified time, no preflight request has to be made.
            $response->headers->set('Access-Control-Max-Age', Config::get('cors.max_age', 32000));
        }
        // The Access-Control-Allow-Headers header indicates, as part of the response to a preflight request,
        // which header field names can be used during the actual request
        $response->headers->set('Access-Control-Allow-Headers', $this->allowed_headers);

        //The Access-Control-Allow-Methods header indicates, as part of the response to a preflight request,
        // which methods can be used during the actual request.
        $response->headers->set('Access-Control-Allow-Methods', $this->allowed_methods);
        // The Access-Control-Request-Headers header indicates which headers will be used in the actual request
        // as part of the preflight request.
        $headers = $request->headers->get('Access-Control-Request-Headers');
        if ($headers) {
            $headers = trim(strtolower($headers));
            $allow_headers = explode(', ', $this->allowed_headers);
            foreach (preg_split('{, *}', $headers) as $header) {
                //if they are simple headers then skip them
                if (in_array($header, self::$simple_headers, true)) {
                    continue;
                }
                //check is the requested header is on the list of allowed headers
                if (!in_array($header, $allow_headers, true)) {
                    $response->setStatusCode(400);
                    $response->setContent('Unauthorized header ' . $header);
                    break;
                }
            }
        }
        //OK - No Content
        $response->setStatusCode(204);
        return $response;
    }

    /**
     * @param Request $request
     * @returns bool
     */
    private function isValidCORSRequest(Request $request)
    {
        /**
         * The presence of the Origin header does not necessarily mean that the request is a cross-origin request.
         * While all cross-origin requests will contain an Origin header,
         * Origin header on same-origin requests. But Chrome and Safari include an Origin header on
         * same-origin POST/PUT/DELETE requests (same-origin GET requests will not have an Origin header).
         */
        return $request->headers->has('Origin');
    }

    /**
     * https://www.owasp.org/index.php/CORS_OriginHeaderScrutiny
     * Filter that will ensure the following points for each incoming HTTP CORS requests:
     *  - Have only one and non empty instance of the origin header,
     *  - Have only one and non empty instance of the host header,
     *  - The value of the origin header is present in a internal allowed domains list (white list). As we act before the
     *    step 2 of the CORS HTTP requests/responses exchange process, allowed domains list is yet provided to client,
     *  - Cache IP of the sender for 1 hour. If the sender send one time a origin domain that is not in the white list
     *    then all is requests will return an HTTP 403 response (protract allowed domain guessing).
     * We use the method above because it's not possible to identify up to 100% that the request come from one expected
     * client application, since:
     *  - All information of a HTTP request can be faked,
     *  - It's the browser (or others tools) that send the HTTP request then the IP address that we have access to is the
     *    client IP address.
     * @param Request $request
     * @return bool
     */
    private function testOriginHeaderScrutiny(Request $request)
    {
        /* Step 0 : Check presence of client IP in black list */
        $client_ip = $request->getClientIp();
        if (Cache::has(self::CORS_IP_BLACKLIST_PREFIX . $client_ip)) {
            return false;
        }
        /* Step 1 : Check that we have only one and non empty instance of the "Origin" header */
        $origin = $request->headers->get('Origin', null, false);
        if (is_array($origin) && count($origin) > 1) {
            // If we reach this point it means that we have multiple instance of the "Origin" header
            // Add client IP address to black listed client
            $expiresAt = Carbon::now()->addMinutes(60);
            Cache::put(self::CORS_IP_BLACKLIST_PREFIX . $client_ip, self::CORS_IP_BLACKLIST_PREFIX . $client_ip, $expiresAt);
            return false;
        }
        /* Step 2 : Check that we have only one and non empty instance of the "Host" header */
        $host = $request->headers->get('Host', null, false);
        //Have only one and non empty instance of the host header,
        if (is_array($host) && count($host) > 1) {
            // If we reach this point it means that we have multiple instance of the "Host" header
            $expiresAt = Carbon::now()->addMinutes(60);
            Cache::put(self::CORS_IP_BLACKLIST_PREFIX . $client_ip, self::CORS_IP_BLACKLIST_PREFIX . $client_ip, $expiresAt);
            return false;
        }
        /* Step 3 : Perform analysis - Origin header is required */

        $origin = $request->headers->get('Origin');
        $host = $request->headers->get('Host');
        $server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
        $origin_host = @parse_url($origin, PHP_URL_HOST);


        // check origin not empty and allowed

        if (!$this->isAllowedOrigin($origin)) {
            $expiresAt = Carbon::now()->addMinutes(60);
            Cache::put(self::CORS_IP_BLACKLIST_PREFIX . $client_ip, self::CORS_IP_BLACKLIST_PREFIX . $client_ip, $expiresAt);
            return false;
        }

        if (is_null($host) || $server_name != $host || is_null($origin_host) || $origin_host == $server_name) {
            $expiresAt = Carbon::now()->addMinutes(60);
            Cache::put(self::CORS_IP_BLACKLIST_PREFIX . $client_ip, self::CORS_IP_BLACKLIST_PREFIX . $client_ip, $expiresAt);
            return false;
        }

        /* Step 4 : Finalize request next step */
        return true;
    }

    private function checkEndPoint($endpoint_path, $http_method)
    {
        $this->current_endpoint = $this->endpoint_repository->getApiEndpointByUrlAndMethod($endpoint_path, $http_method);
        if (is_null($this->current_endpoint)) {
            return false;
        }
        if (!$this->current_endpoint->supportCORS() || !$this->current_endpoint->isActive()) {
            return false;
        }
        return true;
    }

    /**
     * @param string $origin
     * @return bool
     */
    private function isAllowedOrigin($origin)
    {
        return true;
    }

    private static function getRequestType(Request $request)
    {

        $type = CORSRequestPreflightType::UNKNOWN;
        $http_method = $request->getMethod();
        $content_type = strtolower($request->getContentType());
        $http_method = strtoupper($http_method);

        if ($http_method === 'OPTIONS' && $request->headers->has('Access-Control-Request-Method')) {
            $type = CORSRequestPreflightType::REQUEST_FOR_PREFLIGHT;
        } else {
            if (self::hasCustomHeaders($request)) {
                $type = CORSRequestPreflightType::COMPLEX_REQUEST;
            } elseif ($http_method === 'POST' && !in_array($content_type, self::$simple_content_header_values, true)) {
                $type = CORSRequestPreflightType::COMPLEX_REQUEST;
            } elseif (!in_array($http_method, self::$simple_http_methods, true)) {
                $type = CORSRequestPreflightType::COMPLEX_REQUEST;
            } else {
                $type = CORSRequestPreflightType::SIMPLE_REQUEST;
            }
        }
        return $type;
    }


    private static function getCustomHeaders(Request $request)
    {
        $custom_headers = array();
        foreach ($request->headers->all() as $k => $h) {
            if (starts_with('X-', strtoupper(trim($k)))) {
                array_push($custom_headers, strtoupper(trim($k)));
            }
        }
        return $custom_headers;
    }

    private static function hasCustomHeaders(Request $request)
    {
        return count(self::getCustomHeaders($request)) > 0;
    }
}