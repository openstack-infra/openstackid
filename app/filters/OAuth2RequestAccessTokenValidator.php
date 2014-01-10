<?php

use oauth2\services\IApiService;
use oauth2\services\ITokenService;
use oauth2\BearerAccessTokenAuthorizationHeaderParser;
use oauth2\OAuth2Protocol;
use oauth2\responses\OAuth2WWWAuthenticateErrorResponse;
use utils\services\ILogService;
use oauth2\exceptions\OAuth2ResourceServerException;
use oauth2\exceptions\InvalidGrantTypeException;
use utils\services\ICheckPointService;

/**
 * Class OAuth2BearerAccessTokenRequestValidator
 * this class implements the logic to Accessing to Protected Resources
 * http://tools.ietf.org/html/rfc6750
 * http://tools.ietf.org/html/rfc6749#section-7
 */
class OAuth2BearerAccessTokenRequestValidator {

    private $api_service;
    private $token_service;
    private $log_service;
    private $checkpoint_service;

    public function __construct(IApiService $api_service, ITokenService $token_service, ILogService $log_service, ICheckPointService $checkpoint_service){
        $this->api_service         = $api_service;
        $this->token_service       = $token_service;
        $this->log_service         = $log_service;
        $this->checkpoint_service  = $checkpoint_service;
    }

    /**
     * @param $route
     * @param $request
     */
    public function filter($route, $request)
    {

        try{

            $url       = $route->getPath();
            $method    = $request->getMethod();
            $api       = $this->api_service->getApiByUrlAndMethod($url, $method);
            $realm     = $request->getHost();

            //api endpoint must be registered on db
            if(is_null($api)){
                throw new OAuth2ResourceServerException(400,OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,'API endpoint does not exits!');
            }

            //check first http basic auth header
            $auth_header = Request::header('Authorization');
            if(!is_null($auth_header) && !empty($auth_header))
                $access_token_value = BearerAccessTokenAuthorizationHeaderParser::getInstance()->parse($auth_header);
            else{
                // http://tools.ietf.org/html/rfc6750#section-2- 2
                // if access token is not on authorization header check on POST/GET params
                $access_token_value = Input::get(OAuth2Protocol::OAuth2Protocol_AccessToken, '');
            }

            if(is_null($access_token_value) || empty($access_token_value))
            {
                //if access token value is not set, then error
                throw new OAuth2ResourceServerException(400,OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,'missing access token');
            }

            // get access token from service
            $access_token = $this->token_service->getAccessToken($access_token_value);

            //check token audience
            $audience = explode(' ',$access_token->getAudience());
            if((!in_array($realm,$audience)))
                throw new OAuth2ResourceServerException(401,OAuth2Protocol::OAuth2Protocol_Error_InvalidToken,'access token audience does not match');

            //check scopes
            $endpoint_scopes = explode(' ',$api->getScope());
            $token_scopes = explode(' ',$access_token->getScope());
            //check token available scopes vs. endpoint scopes
            if (count(array_diff($endpoint_scopes, $token_scopes)) !== 0)
            {
                $this->log_service->error_msg(sprintf('access token scopes (%s) does not allow to access to api %s',$access_token->getScope(),$url));

                throw new OAuth2ResourceServerException(403,OAuth2Protocol::OAuth2Protocol_Error_InsufficientScope,
                    'the request requires higher privileges than provided by the access token',
                    implode(' ',$endpoint_scopes));
            }

        }
        catch(OAuth2ResourceServerException $ex1){
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
        catch(InvalidGrantTypeException $ex2){
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
        catch(Exception $ex){
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