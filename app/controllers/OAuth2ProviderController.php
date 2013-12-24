<?php

use oauth2\IOAuth2Protocol;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use oauth2\requests\OAuth2TokenRequest;
use oauth2\strategies\OAuth2ResponseStrategyFactoryMethod;

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
        $response  = $this->oauth2_protocol->token( $msg = new OAuth2TokenRequest(Input::all()));
        $reflector = new ReflectionClass($response);
        if ($reflector->isSubclassOf('oauth2\\responses\\OAuth2Response')) {
            $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy($response);
            return $strategy->handle($response);
        }
        return $response;
    }
} 