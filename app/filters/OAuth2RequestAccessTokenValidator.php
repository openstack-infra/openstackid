<?php

use oauth2\services\IApiEndpointService;
use oauth2\services\ITokenService;
use oauth2\BearerAccessTokenAuthorizationHeaderParser;
use oauth2\OAuth2Protocol;
use oauth2\responses\OAuth2WWWAuthenticateErrorResponse;
use utils\services\ILogService;
use oauth2\exceptions\OAuth2ResourceServerException;
use oauth2\exceptions\InvalidGrantTypeException;
use oauth2\exceptions\ExpiredAccessTokenException;
use utils\services\ICheckPointService;
use oauth2\IResourceServerContext;
use oauth2\services\IClientService;
use oauth2\models\IClient;
use utils\http\HttpContentType;
use oauth2\exceptions\RevokedAccessTokenException;

/**
 * Class OAuth2BearerAccessTokenRequestValidator
 * this class implements the logic to Accessing to Protected Resources
 * http://tools.ietf.org/html/rfc6750
 * http://tools.ietf.org/html/rfc6749#section-7
 */
final class OAuth2BearerAccessTokenRequestValidator {

    protected function getHeaders()
    {
        $headers = array();

        if (function_exists('getallheaders')) {
            // @codeCoverageIgnoreStart
            foreach(getallheaders() as $name => $value){
                $headers[strtolower($name)] = $value;
            }
        } else {
            // @codeCoverageIgnoreEnd
            foreach ($_SERVER  as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[strtolower($name)] = $value;
                }
            }

            foreach(Request::header() as $name => $value){
                if(!array_key_exists($name,$headers))
                    $headers[strtolower($name)] = $value[0];
            }
        }
        return $headers;
    }

    /**
     * @var IApiEndpointService
     */
    private $api_endpoint_service;
    /**
     * @var ITokenService
     */
    private $token_service;
    /**
     * @var ILogService
     */
    private $log_service;
    /**
     * @var ICheckPointService
     */
    private $checkpoint_service;
    /**
     * @var IResourceServerContext
     */
    private $resource_server_context;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var IClientService
     */
    private $client_service;

    /**
     * @param IResourceServerContext $resource_server_context
     * @param IApiEndpointService $api_endpoint_service
     * @param ITokenService $token_service
     * @param ILogService $log_service
     * @param ICheckPointService $checkpoint_service
     * @param IClientService $client_service
     */
    public function __construct
    (
        IResourceServerContext $resource_server_context,
        IApiEndpointService $api_endpoint_service,
        ITokenService $token_service,
        ILogService $log_service,
        ICheckPointService $checkpoint_service,
        IClientService $client_service
    )
    {
        $this->api_endpoint_service    = $api_endpoint_service;
        $this->token_service           = $token_service;
        $this->log_service             = $log_service;
        $this->checkpoint_service      = $checkpoint_service;
        $this->resource_server_context = $resource_server_context;
        $this->headers                 = $this->getHeaders();
        $this->client_service          = $client_service;
    }

    /**
     * @param $route
     * @param $request
     */
    public function filter($route, $request)
    {
        $url       = $route->getPath();

        if(strpos($url, '/') != 0)
        {
            $url =   '/'.$url;
        }
        $method    = $request->getMethod();
        $realm     = $request->getHost();
        // http://tools.ietf.org/id/draft-abarth-origin-03.html
        $origin    = $request->headers->has('Origin') ? $request->headers->get('Origin') : null;

        try
        {
            $endpoint  = $this->api_endpoint_service->getApiEndpointByUrlAndMethod($url, $method);

            //api endpoint must be registered on db and active
            if(is_null($endpoint) || !$endpoint->isActive())
            {
                throw new OAuth2ResourceServerException
                (
                    400,
                    OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,
                    sprintf
                    (
                        'API endpoint does not exits! (%s:%s)',
                        $url,
                        $method
                    )
                );
            }

            //check first http basic auth header
            $auth_header = isset($this->headers['authorization'])?$this->headers['authorization']:null;

            if(!is_null($auth_header) && !empty($auth_header))
                $access_token_value = BearerAccessTokenAuthorizationHeaderParser::getInstance()->parse($auth_header);
            else
            {
                // http://tools.ietf.org/html/rfc6750#section-2- 2
                // if access token is not on authorization header check on POST/GET params
                $access_token_value = Input::get(OAuth2Protocol::OAuth2Protocol_AccessToken, '');
            }

            if(is_null($access_token_value) || empty($access_token_value))
            {
                //if access token value is not set, then error
                throw new OAuth2ResourceServerException
                (
                    400,
                    OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,
                    'missing access token'
                );
            }

            // get access token from service
            $access_token = $this->token_service->getAccessToken($access_token_value);
            if(is_null($access_token))
                throw new ExpiredAccessTokenException(sprintf('Access token %s is expired!', $access_token_value));
            //check token audience
            $audience = explode(' ', $access_token->getAudience());

            if((!in_array($realm , $audience)))
                throw new OAuth2ResourceServerException
                (
                    401,
                    OAuth2Protocol::OAuth2Protocol_Error_InvalidToken,
                    sprintf('access token audience does not match - current_realm %s - access token audience %s',$realm, $access_token->getAudience())
                );

            //check client existence
            $client_id = $access_token->getClientId();
            $client    = $this->client_service->getClientById($client_id);

            if(is_null($client))
                throw new OAuth2ResourceServerException
                (
                    400,
                    OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,
                    'invalid client'
                );

            //if js client , then check if the origin is allowed ....
            if($client->getApplicationType() == IClient::ApplicationType_JS_Client)
            {
                if(!$client->isOriginAllowed($origin))
                    throw new OAuth2ResourceServerException
                    (
                        403,
                        OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient,
                        'invalid origin'
                    );
            }
            //check scopes
            $endpoint_scopes = explode(' ',$endpoint->getScope());
            $token_scopes    = explode(' ',$access_token->getScope());

            //check token available scopes vs. endpoint scopes
            if (count(array_intersect($endpoint_scopes, $token_scopes)) == 0)
            {
                $this->log_service->error_msg
                (
                    sprintf
                    (
                        'access token scopes (%s) does not allow to access to api url %s , needed scopes %s',
                        $access_token->getScope(),
                        $url,
                        implode(' OR ',$endpoint_scopes)
                    )
                );

                throw new OAuth2ResourceServerException
                (
                    403,
                    OAuth2Protocol::OAuth2Protocol_Error_InsufficientScope,
                    'the request requires higher privileges than provided by the access token',
                    implode(' ',$endpoint_scopes)
                );
            }

            $context = array
            (
                'access_token' => $access_token_value,
                'expires_in'   => $access_token->getRemainingLifetime(),
                'client_id'    => $client_id,
                'scope'        => $access_token->getScope()
            );

            if(!is_null($access_token->getUserId()))
                $context['user_id'] = $access_token->getUserId();

            $this->resource_server_context->setAuthorizationContext($context);

        }
        catch(OAuth2ResourceServerException $ex1)
        {
            $this->log_service->error($ex1);
            $this->checkpoint_service->trackException($ex1);
            $response = new OAuth2WWWAuthenticateErrorResponse($realm,
                $ex1->getError(),
                $ex1->getErrorDescription(),
                $ex1->getScope(),
                $ex1->getHttpCode()
            );
            $http_response =  Response::json($response->getContent(), $response->getHttpCode());
            $http_response->header('WWW-Authenticate',$response->getWWWAuthenticateHeaderValue());
            return $http_response;
        }
        catch(InvalidGrantTypeException $ex2)
        {
            $this->log_service->error($ex2);
            $this->checkpoint_service->trackException($ex2);
            $response = new OAuth2WWWAuthenticateErrorResponse($realm,
                OAuth2Protocol::OAuth2Protocol_Error_InvalidToken,
                'the access token provided is expired, revoked, malformed, or invalid for other reasons.',
                null,
                401
            );
            $http_response =  Response::json($response->getContent(), $response->getHttpCode());
            $http_response->header('WWW-Authenticate',$response->getWWWAuthenticateHeaderValue());
            return $http_response;
        }
        catch(ExpiredAccessTokenException $ex3)
        {
            $this->log_service->error($ex3);
            $this->checkpoint_service->trackException($ex3);
            $response = new OAuth2WWWAuthenticateErrorResponse($realm,
                OAuth2Protocol::OAuth2Protocol_Error_InvalidToken,
                'the access token provided is expired, revoked, malformed, or invalid for other reasons.',
                null,
                401
            );
            $http_response =  Response::json($response->getContent(), $response->getHttpCode());
            $http_response->header('WWW-Authenticate',$response->getWWWAuthenticateHeaderValue());
            return $http_response;
        }
        catch(RevokedAccessTokenException $ex4)
        {
            $this->log_service->error($ex4);
            $this->checkpoint_service->trackException($ex4);
            $response = new OAuth2WWWAuthenticateErrorResponse($realm,
                OAuth2Protocol::OAuth2Protocol_Error_InvalidToken,
                'the access token provided is expired, revoked, malformed, or invalid for other reasons.',
                null,
                401
            );
            $http_response =  Response::json($response->getContent(), $response->getHttpCode());
            $http_response->header('WWW-Authenticate',$response->getWWWAuthenticateHeaderValue());
            return $http_response;
        }
        catch(Exception $ex)
        {
            $this->log_service->error($ex);
            $this->checkpoint_service->trackException($ex);
            $response = new OAuth2WWWAuthenticateErrorResponse($realm,
                OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,
                'invalid request',
                null,
                400
            );
            $http_response =  Response::json($response->getContent(), $response->getHttpCode());
            $http_response->header('WWW-Authenticate',$response->getWWWAuthenticateHeaderValue());
            return $http_response;
        }
    }
}