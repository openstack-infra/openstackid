<?php

use openid\requests\OpenIdAuthenticationRequest;
use openid\services\IMementoOpenIdRequestService;
use openid\services\IServerConfigurationService;
use openid\services\ITrustedSitesService;
use openid\services\IUserService;
use openid\XRDS\XRDSDocumentBuilder;
use services\IPHelper;
use services\IUserActionService;
use strategies\DefaultLoginStrategy;
use strategies\OpenIdConsentStrategy;
use strategies\OpenIdLoginStrategy;
use utils\services\IAuthService;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use strategies\OAuth2LoginStrategy;
use strategies\OAuth2ConsentStrategy;
use oauth2\services\IClientService;
use oauth2\services\IApiScopeService;

class UserController extends BaseController
{

    private $openid_memento_service;
    private $oauth2_memento_service;
    private $auth_service;
    private $server_configuration_service;
    private $discovery;
    private $user_service;
    private $user_action_service;
    private $login_strategy;
    private $consent_strategy;
    private $client_service;

    public function __construct(IMementoOpenIdRequestService $openid_memento_service,
                                IMementoOAuth2AuthenticationRequestService $oauth2_memento_service,
                                IAuthService $auth_service,
                                IServerConfigurationService $server_configuration_service,
                                ITrustedSitesService $trusted_sites_service,
                                DiscoveryController $discovery,
                                IUserService $user_service,
                                IUserActionService $user_action_service,
                                IClientService $client_service,
                                IApiScopeService $scope_service)
    {
        $this->openid_memento_service       = $openid_memento_service;
        $this->oauth2_memento_service       = $oauth2_memento_service;
        $this->auth_service                 = $auth_service;
        $this->server_configuration_service = $server_configuration_service;
        $this->trusted_sites_service        = $trusted_sites_service;
        $this->discovery                    = $discovery;
        $this->user_service                 = $user_service;
        $this->user_action_service          = $user_action_service;
        $this->client_service               = $client_service;
        //filters
        $this->beforeFilter('csrf', array('only' => array('postLogin', 'postConsent')));

        $openid_msg = $this->openid_memento_service->getCurrentRequest();
        $oauth2_msg = $this->oauth2_memento_service->getCurrentAuthorizationRequest();
        if (!is_null($openid_msg) && $openid_msg->isValid() && OpenIdAuthenticationRequest::IsOpenIdAuthenticationRequest($openid_msg)) {
            //openid stuff
            $this->beforeFilter('openid.save.request');
            $this->beforeFilter('openid.needs.auth.request', array('only' => array('getConsent')));
            $this->login_strategy   = new OpenIdLoginStrategy($openid_memento_service, $user_action_service, $auth_service);
            $this->consent_strategy = new OpenIdConsentStrategy($openid_memento_service, $auth_service, $server_configuration_service, $user_action_service);
        }
        else if(!is_null($oauth2_msg) && $oauth2_msg->isValid()){
            $this->beforeFilter('oauth2.save.request');
            $this->beforeFilter('oauth2.needs.auth.request', array('only' => array('getConsent')));
            $this->login_strategy   = new OAuth2LoginStrategy();
            $this->consent_strategy = new OAuth2ConsentStrategy($auth_service,$oauth2_memento_service,$scope_service,$client_service);
        } else {
            //default stuff
            $this->login_strategy   = new DefaultLoginStrategy($user_action_service, $auth_service);
            $this->consent_strategy = null;
        }

    }

    public function getLogin()
    {
        return $this->login_strategy->getLogin();
    }

    public function cancelLogin()
    {
        return $this->login_strategy->cancelLogin();
    }

    public function postLogin()
    {
        try {
            $max_login_attempts_2_show_captcha = $this->server_configuration_service->getConfigValue("MaxFailed.LoginAttempts.2ShowCaptcha");
            $data = Input::all();
            $login_attempts = intval(Input::get('login_attempts'));
            // Build the validation constraint set.
            $rules = array(
                'username' => 'required|email',
                'password' => 'required',
            );
            if ($login_attempts >= $max_login_attempts_2_show_captcha) {
                $rules['recaptcha_response_field'] = 'required|recaptcha';
            }
            // Create a new validator instance.
            $validator = Validator::make($data, $rules);

            if ($validator->passes()) {

                $username = Input::get("username");
                $password = Input::get("password");
                $remember = Input::get("remember");
                $remember = !is_null($remember);

                if ($this->auth_service->login($username, $password, $remember)) {
                    return $this->login_strategy->postLogin();
                }
                //failed login attempt...
                $user = $this->auth_service->getUserByUsername($username);
                if ($user) {
                    $login_attempts = $user->login_failed_attempt;
                }
                return Redirect::action('UserController@getLogin')->with('max_login_attempts_2_show_captcha', $max_login_attempts_2_show_captcha)->with('login_attempts', $login_attempts)->with('flash_notice', 'Authentication Failed!');
            }
            return Redirect::action('UserController@getLogin')->withErrors($validator);
        } catch (Exception $ex) {
            Log::error($ex);
            return Redirect::action('UserController@getLogin');
        }
    }

    public function getConsent()
    {
        if (is_null($this->consent_strategy))
            return View::make("404");
        return $this->consent_strategy->getConsent();
    }

    public function postConsent()
    {
        try {
            $trust_action = input::get("trust");
            if (!is_null($trust_action) && !is_null($this->consent_strategy)) {
                return $this->consent_strategy->postConsent($trust_action);
            }
            return Redirect::action('UserController@getConsent');
        } catch (Exception $ex) {
            Log::error($ex);
            return Redirect::action('UserController@getConsent');
        }
    }

    public function getIdentity($identifier)
    {
        try {
            $user = $this->auth_service->getUserByOpenId($identifier);
            if (is_null($user))
                return View::make("404");
            //This field contains a semicolon-separated list of representation schemes
            //which will be accepted in the response to this request.
            $accept = Request::header('Accept');
            $accept_values = explode(",", $accept);
            if (in_array(XRDSDocumentBuilder::ContentType, $accept_values)) {
                /*
                * If the Claimed Identifier was not previously discovered by the Relying Party
                * (the "openid.identity" in the request was "http://specs.openid.net/auth/2.0/identifier_select"
                * or a different Identifier, or if the OP is sending an unsolicited positive assertion),
                * the Relying Party MUST perform discovery on the Claimed Identifier in
                * the response to make sure that the OP is authorized to make assertions about the Claimed Identifier.
                */
                return $this->discovery->user($identifier);
            }
            $current_user = $this->auth_service->getCurrentUser();
            $another_user = false;
            if ($current_user && $current_user->getIdentifier() != $user->getIdentifier()) {
                $another_user = true;
            }
            $params = array(
                'show_fullname' => $user->getShowProfileFullName(),
                'username' => $user->getFullName(),
                'show_email' => $user->getShowProfileEmail(),
                'email' => $user->getEmail(),
                'identifier' => $user->getIdentifier(),
                'show_pic' => $user->getShowProfilePic(),
                'pic' => $user->getPic(),
                'another_user' => $another_user,
            );
            return View::make("identity", $params);
        } catch (Exception $ex) {
            Log::error($ex);
            return View::make("404");
        }
    }

    public function logout()
    {
        $this->user_action_service->addUserAction($this->auth_service->getCurrentUser(), IPHelper::getUserIp(), IUserActionService::LogoutAction);
        Auth::logout();
        return Redirect::action("UserController@getLogin");
    }

    public function getProfile()
    {
        $user    = $this->auth_service->getCurrentUser();
        $sites   = $this->trusted_sites_service->getAllTrustedSitesByUser($user);
        $actions = $user->getActions();
        $clients = $user->getClients();

        return View::make("profile", array(
            "username"       => $user->getFullName(),
            "openid_url"     => $this->server_configuration_service->getUserIdentityEndpointURL($user->getIdentifier()),
            "identifier "    => $user->getIdentifier(),
            "sites"          => $sites,
            "show_pic"       => $user->getShowProfilePic(),
            "show_full_name" => $user->getShowProfileFullName(),
            "show_email"     => $user->getShowProfileEmail(),
            'actions'        => $actions,
            'clients'        => $clients,
        ));
    }

    public function get_deleteTrustedSite($id)
    {
        $this->trusted_sites_service->delTrustedSite($id);
        return Redirect::action("UserController@getProfile");
    }

    public function postUserProfileOptions()
    {
        $show_full_name = Input::get("show_full_name");
        $show_email = Input::get("show_email");
        $show_pic = Input::get("show_pic");
        $user = $this->auth_service->getCurrentUser();
        $this->user_service->saveProfileInfo($user->getId(), $show_pic, $show_full_name, $show_email);
        return Redirect::action("UserController@getProfile");
    }

    public function getEditRegisteredClient($id){
        return 'error';
    }

    public function getDeleteRegisteredClient($id){
        return 'error';
    }

    public function postAddRegisteredClient(){
        //$this->client_service->addClient()
        return 'error';
    }
}
