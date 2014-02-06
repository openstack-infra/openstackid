<?php

namespace services\oauth2\CORS;

use oauth2\models\IApiEndpoint;
use oauth2\services\IAllowedOriginService;
use oauth2\services\IApiEndpointService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use utils\services\ICacheService;
use Route;
use App;
use Log;
use Exception;
use Config;

/**
 * Class CORSMiddleware
 * @package services\oauth2
 * Implementation of http://www.w3.org/TR/cors/
 */
class CORSMiddleware {

    private $app;
    private $endpoint_service;
    private $cache_service;
    private $origin_service;
    private $modify_response = false;
    private $headers = array();
    private $allowed_headers;
    private $allowed_methods;
    /**
     * Simple headers as defined in the spec should always be accepted
     */
    protected static $simple_headers = array(
        'accept',
        'accept-language',
        'content-language',
        'origin',
    );

    const DefaultAllowedHeaders =  'origin, content-type, accept, authorization';
    const DefaultAllowedMethods = 'GET, POST, OPTIONS, PUT, DELETE';

    public function __construct(IApiEndpointService $endpoint_service,
                                ICacheService $cache_service,
                                IAllowedOriginService $origin_service)
    {
        $this->endpoint_service  = $endpoint_service;
        $this->cache_service     = $cache_service;
        $this->origin_service    = $origin_service;
        $this->allowed_headers   = Config::get('cors.AllowedHeaders',self::DefaultAllowedHeaders);
        $this->allowed_methods   = Config::get('cors.AllowedMethods',self::DefaultAllowedMethods);
    }

    private function makePreflightResponse(Request $request, IApiEndpoint $endpoint){

        $response = new Response();

        $allow_credentials = Config::get('cors.AllowCredentials', '');
        if(!empty($allow_credentials)){
            $response->headers->set('Access-Control-Allow-Credentials',$allow_credentials );
        }

        if(Config::get('cors.UsePreflightCaching', false)){
            $response->headers->set('Access-Control-Max-Age', Config::get('cors.MaxAge', 32000));
        }

        $response->headers->set('Access-Control-Allow-Headers', $this->allowed_headers);


        if (!$this->checkOrigin($request)) {
            $response->headers->set('Access-Control-Allow-Origin', 'null');
            return $response;
        }
        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin'));

        // check request method
        if ($request->headers->get('Access-Control-Request-Method') != $endpoint->getHttpMethod()) {
            $response->setStatusCode(405);
            return $response;
        }

        $response->headers->set('Access-Control-Allow-Methods', $this->allowed_methods);

        // check request headers
        $allow_headers = explode(', ',$this->allowed_headers);

        $headers = $request->headers->get('Access-Control-Request-Headers');
        if ($headers) {
            $headers = trim(strtolower($headers));
            foreach (preg_split('{, *}', $headers) as $header) {
                if (in_array($header, self::$simple_headers, true)) {
                    continue;
                }
                if (!in_array($header, $allow_headers, true)) {
                    $response->setStatusCode(400);
                    $response->setContent('Unauthorized header '.$header);
                    break;
                }
            }
        }

        $response->setStatusCode(204);
        return $response;
    }

    private function checkOrigin(Request $request)
    {
        // check origin
        $origin = $request->headers->get('Origin');
        if($this->cache_service->getSingleValue($origin)) return true;
        if($origin = $this->origin_service->getByUri($origin)){
            $this->cache_service->addSingleValue($origin,$origin);
            return true;
        }
        Log::warning(sprintf('CORS: origin %s not allowed!',$origin));
        return false;
    }

    public function verifyRequest($request){
        try{
            // skip if not a CORS request
            if (!$request->headers->has('Origin')) {
                return;
            }

            $method    = $request->getMethod();
            $preflight = false;

            //preflight checks
            if ($method === 'OPTIONS') {
                $request_method  = $request->headers->get('Access-Control-Request-Method');
                if(is_null($request_method)){
                    Log::warning('CORS: not a valid preflight request!');
                    return;
                }
                // sets the original method on request in order to be able to find the
                // correct route
                $request->setMethod($request_method);
                $preflight = true;
            }

            //gets routes from container and try to find the route
            $router    = App::make('router');
            $routes    = $router->getRoutes();
            $route     = $routes->match($request);

            $url       = $route->getPath();

            if(strpos($url, '/') != 0){
                $url =   '/'.$url;
            }

            $endpoint = $this->endpoint_service->getApiEndpointByUrl($url);
            //check if api endpoint exists or not, if active and if supports cors
            if(is_null($endpoint) || !$endpoint->isActive() || !$endpoint->supportCORS()){

                if(is_null($endpoint)){
                    Log::warning(sprintf("does not exists an endpoint for url %s.",$url));
                }
                else if(!$endpoint->isActive()){
                    Log::warning(sprintf("endpoint %s is not active.",$url));
                }
                else if(!$endpoint->supportCORS()){
                    Log::warning(sprintf("endpoint %s does not support CORS.",$url));
                }

                return;
            }

            // perform preflight checks
            if ($preflight) {
                return $this->makePreflightResponse($request,$endpoint);
            }

            if (!$this->checkOrigin($request)) {
                return new Response('', 403, array('Access-Control-Allow-Origin' => 'null'));
            }

            $this->modify_response = true;

            // Save response headers
            $this->headers['Access-Control-Allow-Origin'] =  $request->headers->get('Origin');
            $this->headers['Access-Control-Allow-Credentials'] = 'true';
        }
        catch(Exception $ex){
            Log::error($ex);
        }
    }

    public function modifyResponse($request,$response)
    {
        if(!$this->modify_response){
            return $response;
        }
        // add CORS response headers
        Log::info('CORS: Adding CORS HEADERS.');
        $response->headers->add($this->headers);
        return $response;
    }

}