<?php

use oauth2\IOAuth2Protocol;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use oauth2\requests\OAuth2TokenRequest;
use oauth2\strategies\OAuth2ResponseStrategyFactoryMethod;
use oauth2\OAuth2Message;
use oauth2\requests\OAuth2TokenRevocationRequest;
use oauth2\requests\OAuth2AccessTokenValidationRequest;

/**
 * Class OAuth2ProviderController
 */
class OAuth2ProviderController extends BaseController {

    private $oauth2_protocol;
    private $memento_service;

    /**
     * @param IOAuth2Protocol $oauth2_protocol
     * @param IMementoOAuth2AuthenticationRequestService $memento_service
     */
    public function __construct(IOAuth2Protocol $oauth2_protocol, IMementoOAuth2AuthenticationRequestService $memento_service){
        $this->oauth2_protocol = $oauth2_protocol;
        $this->memento_service = $memento_service;
    }

    /**
     * Authorize HTTP Endpoint
     * @return mixed
     */
    public function authorize(){
        $request   = $this->memento_service->getCurrentAuthorizationRequest();
        $response  = $this->oauth2_protocol->authorize($request);
        $reflector = new ReflectionClass($response);
        if ($reflector->isSubclassOf('oauth2\\responses\\OAuth2Response')) {
            $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy($response);
            return $strategy->handle($response);
        }
        return $response;
    }

    /**
     * Token HTTP Endpoint
     * @return mixed
     */
    public function token(){
        $response  = $this->oauth2_protocol->token(new OAuth2TokenRequest(new OAuth2Message(Input::all())));
        $reflector = new ReflectionClass($response);
        if ($reflector->isSubclassOf('oauth2\\responses\\OAuth2Response')) {
            $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy($response);
            return $strategy->handle($response);
        }
        return $response;
    }

    /**
     * Revoke Token HTTP Endpoint
     * @return mixed
     */
    public function revoke(){
        $response  = $this->oauth2_protocol->revoke(new OAuth2TokenRevocationRequest(new OAuth2Message(Input::all())));
        $reflector = new ReflectionClass($response);
        if ($reflector->isSubclassOf('oauth2\\responses\\OAuth2Response')) {
            $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy($response);
            return $strategy->handle($response);
        }
        return $response;
    }

    /**
     * http://tools.ietf.org/html/draft-richer-oauth-introspection-04
     * Introspection Token HTTP Endpoint
     * @return mixed
     */
    public function introspection(){
        $response  = $this->oauth2_protocol->introspection(new OAuth2AccessTokenValidationRequest(new OAuth2Message(Input::all())));
        $reflector = new ReflectionClass($response);
        if ($reflector->isSubclassOf('oauth2\\responses\\OAuth2Response')) {
            $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy($response);
            return $strategy->handle($response);
        }
        return $response;
    }
} 