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
use Illuminate\Support\Facades\Cache;
/**
 * Class CORSMiddleware
 * @package services\oauth2
 * Implementation of http://www.w3.org/TR/cors/
 */
class CORSMiddleware {

    private $endpoint_service;
    private $cache_service;
    private $origin_service;
    private $actual_request = false;
    private $headers        = array();
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

    const DefaultAllowedHeaders = 'origin, content-type, accept, authorization, x-requested-with';
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

    /**
     * User agents can discover via a preflight request whether a cross-origin resource is prepared to
     * accept requests, using a non-simple method, from a given origin.
     * @param Request $request
     * @param IApiEndpoint $endpoint
     * @return Response
     */
    private function makePreflightResponse(Request $request, IApiEndpoint $endpoint){

        $response = new Response();

        $allow_credentials = Config::get('cors.AllowCredentials', '');

        if(!empty($allow_credentials)){
            // The Access-Control-Allow-Credentials header indicates whether the response to request
            // can be exposed when the omit credentials flag is unset. When part of the response to a preflight request
            // it indicates that the actual request can include user credentials.
            $response->headers->set('Access-Control-Allow-Credentials',$allow_credentials );
        }

        if(Config::get('cors.UsePreflightCaching', false)){
            // The Access-Control-Max-Age header indicates how long the response can be cached, so that for
            // subsequent requests, within the specified time, no preflight request has to be made.
            $response->headers->set('Access-Control-Max-Age', Config::get('cors.MaxAge', 32000));
        }
        // The Access-Control-Allow-Headers header indicates, as part of the response to a preflight request,
        // which header field names can be used during the actual request
        $response->headers->set('Access-Control-Allow-Headers', $this->allowed_headers);

        if (!$this->checkOrigin($request)) {
            $response->headers->set('Access-Control-Allow-Origin', 'null');
            $response->setStatusCode(403);
            return $response;
        }

        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin'));

        // The Access-Control-Request-Method header indicates which method will be used in the actual
        // request as part of the preflight request
        // check request method

        if ($request->headers->get('Access-Control-Request-Method') != $endpoint->getHttpMethod()) {
            $response->setStatusCode(405);
            return $response;
        }
        //The Access-Control-Allow-Methods header indicates, as part of the response to a preflight request,
        // which methods can be used during the actual request.
        $response->headers->set('Access-Control-Allow-Methods', $this->allowed_methods);

        // The Access-Control-Request-Headers header indicates which headers will be used in the actual request
        // as part of the preflight request.

        $headers = $request->headers->get('Access-Control-Request-Headers');

        if ($headers) {
            $headers       = trim(strtolower($headers));
            $allow_headers = explode(', ',$this->allowed_headers);
            foreach (preg_split('{, *}', $headers) as $header) {
                //if they are simple headers then skip them
                if (in_array($header, self::$simple_headers, true)) {
                    continue;
                }
                //check is the requested header is on the list of allowed headers
                if (!in_array($header, $allow_headers, true)) {
                    $response->setStatusCode(400);
                    $response->setContent('Unauthorized header '.$header);
                    break;
                }
            }
        }
        //OK - No Content
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
            /**
             * The presence of the Origin header does not necessarily mean that the request is a cross-origin request.
             * While all cross-origin requests will contain an Origin header,
             * some same-origin requests might have one as well. For example, Firefox doesn't include an
             * Origin header on same-origin requests. But Chrome and Safari include an Origin header on
             * same-origin POST/PUT/DELETE requests (same-origin GET requests will not have an Origin header).
             */

            if (!$request->headers->has('Origin')) {
                return;
            }
            //https://www.owasp.org/index.php/CORS_OriginHeaderScrutiny
            $origin      = $request->headers->get('Origin',null,false);
            $host        = $request->headers->get('Host',null,false);
            if(is_array($origin) && count($origin)>1){
                // If we reach this point it means that we have multiple instance of the "Origin" header
                $response = new Response();
                $response->setStatusCode(403);
                return $response;
            }
            //now get the first one
            $origin      = $request->headers->get('Origin');
            $server_name = isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:null;
            $origin_host = @parse_url($origin,PHP_URL_HOST);

            //Have only one and non empty instance of the host header,
            if(is_array($host) && count($host)>1){
                // If we reach this point it means that we have multiple instance of the "Host" header
                $response = new Response();
                $response->setStatusCode(403);
                return;
            }
            //now get the first one
            $host = $request->headers->get('Host');

            if(is_null($host) || $server_name != $host || is_null($origin_host) || $origin_host == $server_name){
                return;
            }

            $method    = $request->getMethod();
            $preflight = false;

            //preflight checks
            if ($method == 'OPTIONS') {
                $request_method  = $request->headers->get('Access-Control-Request-Method');
                if(!is_null($request_method)){
                    // sets the original method on request in order to be able to find the
                    // correct route
                    $request->setMethod($request_method);
                    $preflight = true;
                }
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

            //perform preflight checks
            if ($preflight) {
                return $this->makePreflightResponse($request,$endpoint);
            }
            //Actual Request
            if (!$this->checkOrigin($request)) {
                return new Response('', 403, array('Access-Control-Allow-Origin' => 'null'));
            }

            $this->actual_request = true;

            // Save response headers
            $this->headers['Access-Control-Allow-Origin'] =  $request->headers->get('Origin');
            $allow_credentials = Config::get('cors.AllowCredentials', '');
            if(!empty($allow_credentials)){
                // The Access-Control-Allow-Credentials header indicates whether the response to request
                // can be exposed when the omit credentials flag is unset. When part of the response to a preflight request
                // it indicates that the actual request can include user credentials.
                $this->headers['Access-Control-Allow-Credentials'] = $allow_credentials ;
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
            $exposed_headers = Config::get('cors.ExposedHeaders', '');
            if(!empty($exposed_headers)){
                $this->headers['Access-Control-Expose-Headers'] = $exposed_headers ;
            }

        }
        catch(Exception $ex){
            Log::error($ex);
        }
    }

    public function modifyResponse($request, $response)
    {
        if(!$this->actual_request){
            return $response;
        }
        // add CORS response headers
        Log::info('CORS: Adding CORS HEADERS.');
        $response->headers->add($this->headers);
        return $response;
    }

}