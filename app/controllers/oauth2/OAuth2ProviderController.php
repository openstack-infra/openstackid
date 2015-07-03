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
use utils\services\IAuthService;
use utils\http\HttpContentType;
use oauth2\requests\OAuth2LogoutRequest;
use oauth2\exceptions\UriNotAllowedException;
use \oauth2\services\IClientService;
/**
 * Class OAuth2ProviderController
 */
final class OAuth2ProviderController extends BaseController
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
     * @var IAuthService
     */
    private $auth_service;

    /**
     * @var IClientService
     */
    private $client_service;

    /**
     * @param IOAuth2Protocol $oauth2_protocol
     * @param IMementoOAuth2SerializerService $memento_service
     * @param IClientService $client_service
     * @param IAuthService $auth_service
     */
    public function __construct
    (
        IOAuth2Protocol $oauth2_protocol,
        IMementoOAuth2SerializerService $memento_service,
        IClientService $client_service,
        IAuthService $auth_service
    )
    {
        $this->oauth2_protocol = $oauth2_protocol;
        $this->memento_service = $memento_service;
        $this->auth_service    = $auth_service;
        $this->client_service  = $client_service;
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
        try
        {
            $msg = new OAuth2Message(Input::all());

            if ($this->memento_service->exists()) {
                $msg = OAuth2Message::buildFromMemento($this->memento_service->load());
            }

            $request = OAuth2AuthorizationRequestFactory::getInstance()->build($msg);

            $response = $this->oauth2_protocol->authorize($request);

            if ($response instanceof OAuth2Response) {
                $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy($request, $response);

                return $strategy->handle($response);
            }

            return $response;
        }
        catch(UriNotAllowedException $ex1)
        {
            return Response::view
            (
                '400',
                array
                (
                    'error_code' => $ex1->getError(),
                    'error_description' => $ex1->getMessage()
                ),
                400
            );
        }
    }

    /**
     * Token HTTP Endpoint
     * @return mixed
     */
    public function token()
    {

        $request = new OAuth2TokenRequest
        (
            new OAuth2Message
            (
                Input::all()
            )
        );

        $response  = $this->oauth2_protocol->token($request);

        if ($response instanceof OAuth2Response)
        {
            $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy($request, $response);
            return $strategy->handle($response);
        }

        return $response;
    }

    /**
     * Revoke Token HTTP Endpoint
     * @return mixed
     */
    public function revoke()
    {
        $request  = new OAuth2TokenRevocationRequest
        (
            new OAuth2Message
            (
                Input::all()
            )
        );

        $response = $this->oauth2_protocol->revoke($request);

        if ($response instanceof OAuth2Response)
        {
            $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy($request, $response);
            return $strategy->handle($response);
        }

        return $response;
    }

    /**
     * http://tools.ietf.org/html/draft-richer-oauth-introspection-04
     * Introspection Token HTTP Endpoint
     * @return mixed
     */
    public function introspection()
    {
        $request = new OAuth2AccessTokenValidationRequest
        (
            new OAuth2Message
            (
                Input::all()
            )
        );

        $response = $this->oauth2_protocol->introspection($request);

        if ($response instanceof OAuth2Response)
        {
            $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy($request, $response);
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
        $response->header('Content-Type', HttpContentType::Json);

        return $response;
    }

    public function discovery()
    {

        $doc      = $this->oauth2_protocol->getDiscoveryDocument();
        $response = Response::make($doc, 200);
        $response->header('Content-Type', HttpContentType::Json);

        return $response;
    }

    /**
     *  http://openid.net/specs/openid-connect-session-1_0.html#OPiframe
     */
    public function checkSessionIFrame()
    {
        $data = array();
        return View::make("oauth2.session.check-session", $data);
    }

    /**
     * http://openid.net/specs/openid-connect-session-1_0.html#RPLogout
     */
    public function endSession()
    {
        if(!$this->auth_service->isUserLogged())
            return Response::view('404', array(), 404);

        $request = new OAuth2LogoutRequest
        (
            new OAuth2Message
            (
                Input::all()
            )
        );

        if(!$request->isValid())
        {
            Log::error('invalid OAuth2LogoutRequest!');
            return Response::view('404', array(), 404);
        }

        if(Request::isMethod('get') )
        {
            $rps     = $this->auth_service->getLoggedRPs();
            $clients = array();
            foreach($this->auth_service->getLoggedRPs() as $client_id)
            {
                $client = $this->client_service->getClientById($client_id);
                if(!is_null($client)) array_push($clients, $client);
            }

            // At the logout endpoint, the OP SHOULD ask the End-User whether he wants to log out of the OP as well.
            // If the End-User says "yes", then the OP MUST log out the End-User.
            return View::make('oauth2.session.session-logout', array
            (
                'clients'                  => $clients,
                'id_token_hint'            => $request->getIdTokenHint(),
                'post_logout_redirect_uri' => $request->getPostLogoutRedirectUri(),
                'state'                    => $request->getState(),
            ));
        }

        $consent = Input::get('oidc_endsession_consent');

        if($consent === '1')
        {
            $response = $this->oauth2_protocol->endSession($request);

            if (!is_null($response) && $response instanceof OAuth2Response) {
                $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy($request, $response);

                return $strategy->handle($response);
            }

            return View::make('oauth2.session.session-ended');
        }

        Log::error('invalid consent response!');
        return Response::view('404', array(), 404);
    }

    public function cancelLogout()
    {
        return Redirect::action('HomeController@index');
    }
} 