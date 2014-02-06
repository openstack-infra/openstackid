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

/**
 * Class CORSMiddleware
 * @package services\oauth2
 */
class CORSMiddleware {

    protected $app;
    private $endpoint_service;
    private $cache_service;
    private $origin_service;
    private $modify_response = false;
    protected $headers = array();
    /**
     * Simple headers as defined in the spec should always be accepted
     */
    protected static $simple_headers = array(
        'accept',
        'accept-language',
        'content-language',
        'origin',
    );

    const MaxAge = 32000;
    const AllowedHeaders =  'origin, content-type, accept, authorization';
    const AllowedMethods = 'GET, POST, OPTIONS, PUT, DELETE';

    public function __construct(IApiEndpointService $endpoint_service,
                                ICacheService $cache_service,
                                IAllowedOriginService $origin_service)
    {
        $this->endpoint_service = $endpoint_service;
        $this->cache_service    = $cache_service;
        $this->origin_service   = $origin_service;
    }

    private function makePreflightResponse(Request $request, IApiEndpoint $endpoint){

        $response = new Response();
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        $response->headers->set('Access-Control-Allow-Headers', self::AllowedHeaders);
        $response->headers->set('Access-Control-Max-Age', self::MaxAge);

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

        $response->headers->set('Access-Control-Allow-Methods', self::AllowedMethods);

        // check request headers
        $allow_headers = explode(', ',self::AllowedHeaders);

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
            $this->cache_service->addSingleValue($origin,1);
            return true;
        }
        return false;
    }

    public function verifyRequest($request){
        // skip if not a CORS request
        if (!$request->headers->has('Origin')) {
            return;
        }

        $router    = App::make('router');
        $routes    = $router->getRoutes();
        $method    = $request->getMethod();
        $route     = null;
        $preflight = false;
        //preflight checks
        if ($method === 'OPTIONS') {
            $request->setMethod($request->headers->get('Access-Control-Request-Method'));
            $preflight = true;
        }

        $route  = $routes->match($request);

        $url    = $route->getPath();

        if(strpos($url, '/') != 0){
            $url =   '/'.$url;
        }

        $endpoint = $this->endpoint_service->getApiEndpointByUrl($url);
        //check if api endpoint exists or not, if active and if supports cors
        if(is_null($endpoint) || !$endpoint->isActive() || !$endpoint->supportCORS())
            return;

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

        return;
    }

    public function modifyResponse($request,$response)
    {
        if(!$this->modify_response){
            return $response;
        }
        // add CORS response headers
        $response->headers->add($this->headers);
        return $response;
    }

}