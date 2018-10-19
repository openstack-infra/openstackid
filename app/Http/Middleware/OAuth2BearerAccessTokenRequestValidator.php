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
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use OAuth2\BearerAccessTokenAuthorizationHeaderParser;
use OAuth2\Exceptions\ExpiredAccessTokenException;
use OAuth2\Exceptions\InvalidGrantTypeException;
use OAuth2\Exceptions\RevokedAccessTokenException;
use OAuth2\Models\IClient;
use OAuth2\OAuth2Protocol;
use OAuth2\Exceptions\OAuth2ResourceServerException;
use OAuth2\Repositories\IClientRepository;
use OAuth2\Responses\OAuth2WWWAuthenticateErrorResponse;
use OAuth2\Services\ITokenService;
use OAuth2\IResourceServerContext;
use OAuth2\Repositories\IApiEndpointRepository;
use OAuth2\Services\IClientService;
use URL\Normalizer;
use Illuminate\Support\Facades\Route;
use Exception;
use Utils\Services\ICheckPointService;
use Utils\Services\ILogService;

/**
 * Class OAuth2BearerAccessTokenRequestValidator
 * this class implements the logic to Accessing to Protected Resources
 * @see http://tools.ietf.org/html/rfc6750
 * @see http://tools.ietf.org/html/rfc6749#section-7
 * @package App\Http\Middleware
 */
final class OAuth2BearerAccessTokenRequestValidator
{

    /**
     * @var IResourceServerContext
     */
    private $context;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var IApiEndpointRepository
     */
    private $endpoint_repository;

    /**
     * @var ITokenService
     */
    private $token_service;

    /**
     * @var IClientRepository
     */
    private $client_repository;

    /**
     * @var ILogService
     */
    private $log_service;
    /**
     * @var ICheckPointService
     */
    private $checkpoint_service;

    /**
     * OAuth2BearerAccessTokenRequestValidator constructor.
     * @param IResourceServerContext $context
     * @param IApiEndpointRepository $endpoint_repository
     * @param ITokenService $token_service
     * @param IClientRepository $client_repository
     * @param ILogService $log_service
     * @param ICheckPointService $checkpoint_service
     */
    public function __construct(
        IResourceServerContext $context,
        IApiEndpointRepository $endpoint_repository,
        ITokenService $token_service,
        IClientRepository $client_repository,
        ILogService $log_service,
        ICheckPointService $checkpoint_service
    ) {
        $this->context             = $context;
        $this->headers             = $this->getHeaders();
        $this->endpoint_repository = $endpoint_repository;
        $this->token_service       = $token_service;
        $this->client_repository   = $client_repository;
        $this->log_service         = $log_service;
        $this->checkpoint_service  = $checkpoint_service;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return OAuth2WWWAuthenticateErrorResponse
     */
    public function handle($request, Closure $next)
    {
        $url    = $request->getRequestUri();
        $method = $request->getMethod();
        $realm  = $request->getHost();

        try {
            $route_path  = Route::getCurrentRoute()->uri();
            if (strpos($route_path, '/') != 0)
                $route_path = '/' . $route_path;

            if (!$route_path) {
                throw new OAuth2ResourceServerException(
                    400,
                    OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,
                    sprintf('API endpoint does not exits! (%s:%s)', $url, $method)
                );
            }

            Log::debug($request->headers->__toString());
            // http://tools.ietf.org/id/draft-abarth-origin-03.html
            $origin = $request->headers->has('Origin') ? $request->headers->get('Origin') : null;
            if (!empty($origin)) {
                $nm     = new Normalizer($origin);
                $origin = $nm->normalize();
            }

            //check first http basic auth header
            $auth_header = isset($this->headers['authorization']) ? $this->headers['authorization'] : null;
            if (!is_null($auth_header) && !empty($auth_header)) {
                $access_token_value = BearerAccessTokenAuthorizationHeaderParser::getInstance()->parse($auth_header);
            } else {
                // http://tools.ietf.org/html/rfc6750#section-2- 2
                // if access token is not on authorization header check on POST/GET params
                $access_token_value = Input::get(OAuth2Protocol::OAuth2Protocol_AccessToken, '');
            }

            if (is_null($access_token_value) || empty($access_token_value)) {
                //if access token value is not set, then error
                throw new OAuth2ResourceServerException(
                    400,
                    OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,
                    'missing access token'
                );
            }

            $endpoint = $this->endpoint_repository->getApiEndpointByUrlAndMethod($route_path, $method);

            //api endpoint must be registered on db and active
            if (is_null($endpoint) || !$endpoint->isActive()) {
                throw new OAuth2ResourceServerException(
                    400,
                    OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,
                    sprintf('API endpoint does not exits! (%s:%s)', $route_path, $method)
                );
            }

            $access_token = $this->token_service->getAccessToken($access_token_value);
            //check lifetime
            if (is_null($access_token)) {
                throw new InvalidGrantTypeException(OAuth2Protocol::OAuth2Protocol_Error_InvalidToken);
            }
            Log::debug(sprintf("token lifetime %s", $access_token->getRemainingLifetime()));
            //check token audience
            Log::debug('checking token audience ...');
            $audience = explode(' ', $access_token->getAudience());
            if ((!in_array($realm, $audience))) {
                throw new InvalidGrantTypeException(OAuth2Protocol::OAuth2Protocol_Error_InvalidToken);
            }

            //check client existence
            $client_id = $access_token->getClientId();
            $client    = $this->client_repository->getClientById($client_id);

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
            Log::debug('checking token scopes ...');
            $endpoint_scopes = explode(' ', $endpoint->getScope());
            $token_scopes    = explode(' ', $access_token->getScope());

            //check token available scopes vs. endpoint scopes
            if (count(array_intersect($endpoint_scopes, $token_scopes)) == 0) {
                Log::warning(
                    sprintf(
                        'access token scopes (%s) does not allow to access to api url %s , needed scopes %s',
                        $access_token->getScope(),
                        $url,
                        implode(' OR ', $endpoint_scopes)
                    )
                );

                throw new OAuth2ResourceServerException(
                    403,
                    OAuth2Protocol::OAuth2Protocol_Error_InsufficientScope,
                    'the request requires higher privileges than provided by the access token',
                    implode(' ', $endpoint_scopes)
                );
            }
            Log::debug('setting resource server context ...');
            //set context for api and continue processing
            $context = array
            (
                'access_token'     => $access_token_value,
                'expires_in'       => $access_token->getRemainingLifetime(),
                'client_id'        => $client_id,
                'scope'            => $access_token->getScope(),
                'application_type' => $client->getApplicationType()
            );

            if (!is_null($access_token->getUserId()))
            {
                $context['user_id']          = $access_token->getUserId();
                //$context['user_external_id'] = $access_token->getUserExternalId();
            }

            $this->context->setAuthorizationContext($context);

        }
        catch(OAuth2ResourceServerException $ex1)
        {
            $this->log_service->warning($ex1);
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
            $this->log_service->warning($ex2);
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
            $this->log_service->warning($ex3);
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
            $this->log_service->warning($ex4);
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
        $response = $next($request);

        return $response;
    }

    /**
     * @return array
     */
    protected function getHeaders()
    {
        $headers = array();
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                $headers[strtolower($name)] = $value;
            }
        } else {
            // @codeCoverageIgnoreEnd
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[strtolower($name)] = $value;
                }
            }
            foreach (Request::header() as $name => $value) {
                if (!array_key_exists($name, $headers)) {
                    $headers[strtolower($name)] = $value[0];
                }
            }
        }

        return $headers;
    }
}