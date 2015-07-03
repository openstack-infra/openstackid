<?php

use oauth2\IOAuth2Protocol;
use oauth2\requests\OAuth2TokenRequest;
use oauth2\strategies\OAuth2ResponseStrategyFactoryMethod;
use oauth2\OAuth2Message;
use oauth2\requests\OAuth2TokenRevocationRequest;
use oauth2\requests\OAuth2AccessTokenValidationRequest;
use oauth2\responses\OAuth2Response;
use oauth2\factories\OAuth2AuthorizationRequestFactory;
use oauth2\services\IMementoOAuth2SerializerService;
use oauth2\exceptions\InvalidAuthorizationRequestException;
/**
 * Class OAuth2ProviderController
 */
class OAuth2ProviderController extends BaseController
{

    /**
     * @var IOAuth2Protocol
     */
    private $oauth2_protocol;

    /**
     * @var IMementoOAuth2SerializerService
     */
    private $memento_service;

    /**
     * @param IOAuth2Protocol $oauth2_protocol
     * @param IMementoOAuth2SerializerService $memento_service
     */
    public function __construct(IOAuth2Protocol $oauth2_protocol, IMementoOAuth2SerializerService $memento_service)
    {
        $this->oauth2_protocol = $oauth2_protocol;
        $this->memento_service = $memento_service;
    }

    /**
     * Authorize HTTP Endpoint
     * The authorization server MUST support the use of the HTTP "GET"
     * method [RFC2616] for the authorization endpoint and MAY support the
     * use of the "POST" method as well.
     * @return mixed
     */
    public function authorize()
    {
        $msg = new OAuth2Message(Input::all());

        if($this->memento_service->exists()){
            $msg = OAuth2Message::buildFromMemento( $this->memento_service->load());
        }

        $response = $this->oauth2_protocol->authorize( OAuth2AuthorizationRequestFactory::getInstance()->build($msg) );

        if ($response instanceof OAuth2Response )
        {
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

        $response  = $this->oauth2_protocol->token
        (
            new OAuth2TokenRequest(
                new OAuth2Message(
                    Input::all()
                )
            )
        );

        if ($response instanceof OAuth2Response )
        {
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

        $response  = $this->oauth2_protocol->revoke(
            new OAuth2TokenRevocationRequest(
                new OAuth2Message(
                    Input::all())
            )
        );

        if ($response instanceof OAuth2Response )
        {
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

        $response  = $this->oauth2_protocol->introspection(
            new OAuth2AccessTokenValidationRequest(
                new OAuth2Message(
                    Input::all()
                )
            )
        );

        if ($response instanceof OAuth2Response )
        {
            $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy($response);
            return $strategy->handle($response);
        }

        return $response;
    }

    /**
     *  OP's JSON Web Key Set [JWK] document.
     * @return string
     */
    public function certs()
    {

        $doc      = $this->oauth2_protocol->getJWKSDocument();
        $response = Response::make($doc, 200);
        $response->header('Content-Type', 'application/json');

        return $response;
    }

    public function discovery()
    {

        $doc      = $this->oauth2_protocol->getDiscoveryDocument();
        $response = Response::make($doc, 200);
        $response->header('Content-Type', 'application/json');

        return $response;
    }
} 