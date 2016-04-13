<?php namespace App\Http\Controllers\OAuth2;
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

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use OAuth2\Exceptions\UriNotAllowedException;
use OAuth2\Factories\OAuth2AuthorizationRequestFactory;
use OAuth2\IOAuth2Protocol;
use OAuth2\OAuth2Message;
use OAuth2\Requests\OAuth2AccessTokenValidationRequest;
use OAuth2\Requests\OAuth2LogoutRequest;
use OAuth2\Requests\OAuth2TokenRequest;
use OAuth2\Requests\OAuth2TokenRevocationRequest;
use OAuth2\Responses\OAuth2Response;
use OAuth2\Services\IClientService;
use OAuth2\Strategies\OAuth2ResponseStrategyFactoryMethod;
use Utils\Http\HttpContentType;
use Utils\Services\IAuthService;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

/**
 * Class OAuth2ProviderController
 */
final class OAuth2ProviderController extends Controller
{
    /**
     * @var IOAuth2Protocol
     */
    private $oauth2_protocol;

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
     * @param IClientService $client_service
     * @param IAuthService $auth_service
     */
    public function __construct
    (
        IOAuth2Protocol $oauth2_protocol,
        IClientService $client_service,
        IAuthService $auth_service
    )
    {
        $this->oauth2_protocol = $oauth2_protocol;
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
            $response = $this->oauth2_protocol->authorize
            (
                OAuth2AuthorizationRequestFactory::getInstance()->build
                (
                    new OAuth2Message
                    (
                        Input::all()
                    )
                )
            );

            if ($response instanceof OAuth2Response) {
                $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy
                (
                    $this->oauth2_protocol->getLastRequest(),
                    $response
                );
                return $strategy->handle($response);
            }

            return $response;
        }
        catch(UriNotAllowedException $ex1)
        {
            return Response::view
            (
                'error.400',
                array
                (
                    'error_code'        => $ex1->getError(),
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

        $response  = $this->oauth2_protocol->token
        (
            new OAuth2TokenRequest
            (
                new OAuth2Message
                (
                    Input::all()
                )
            )
        );

        if ($response instanceof OAuth2Response)
        {
            $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy
            (
                $this->oauth2_protocol->getLastRequest(),
                $response
            );
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
        $response = $this->oauth2_protocol->revoke
        (
            new OAuth2TokenRevocationRequest
            (
                new OAuth2Message
                (
                    Input::all()
                )
            )
        );

        if ($response instanceof OAuth2Response)
        {
            $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy
            (
                $this->oauth2_protocol->getLastRequest(),
                $response
            );
            return $strategy->handle($response);
        }

        return $response;
    }

    /**
     * @see http://tools.ietf.org/html/draft-richer-oauth-introspection-04
     * Introspection Token HTTP Endpoint
     * @return mixed
     */
    public function introspection()
    {

        $response = $this->oauth2_protocol->introspection
        (
            new OAuth2AccessTokenValidationRequest
            (
                new OAuth2Message
                (
                    Input::all()
                )
            )
        );

        if ($response instanceof OAuth2Response)
        {
            $strategy = OAuth2ResponseStrategyFactoryMethod::buildStrategy
            (
                $this->oauth2_protocol->getLastRequest(),
                $response
            );
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
     *  @see http://openid.net/specs/openid-connect-session-1_0.html#OPiframe
     */
    public function checkSessionIFrame()
    {
        $data = array();
        return View::make("oauth2.session.check-session", $data);
    }

    /**
     * @see http://openid.net/specs/openid-connect-session-1_0.html#RPLogout
     */
    public function endSession()
    {
        if(!$this->auth_service->isUserLogged())
            return Response::view('error.404', array(), 404);

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
            return Response::view('error.404', array(), 404);
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
        return Response::view('error.404', array(), 404);
    }

    public function cancelLogout()
    {
        return Redirect::action('HomeController@index');
    }
} 