<?php

use oauth2\services\IApiScopeService;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use oauth2\exceptions\AllowedClientUriAlreadyExistsException;
use openid\requests\OpenIdAuthenticationRequest;
use openid\services\IMementoOpenIdRequestService;
use openid\services\IServerConfigurationService;
use openid\services\ITrustedSitesService;
use openid\services\IUserService;
use openid\XRDS\XRDSDocumentBuilder;
use services\IPHelper;
use services\IUserActionService;
use strategies\DefaultLoginStrategy;
use strategies\OAuth2ConsentStrategy;
use strategies\OAuth2LoginStrategy;
use strategies\OpenIdConsentStrategy;
use strategies\OpenIdLoginStrategy;
use utils\services\IAuthService;


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
    private $scope_service;

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
        $this->openid_memento_service = $openid_memento_service;
        $this->oauth2_memento_service = $oauth2_memento_service;
        $this->auth_service = $auth_service;
        $this->server_configuration_service = $server_configuration_service;
        $this->trusted_sites_service = $trusted_sites_service;
        $this->discovery = $discovery;
        $this->user_service = $user_service;
        $this->user_action_service = $user_action_service;
        $this->client_service = $client_service;
        $this->scope_service = $scope_service;
        //filters
        $this->beforeFilter('csrf', array('only' => array('postLogin', 'postConsent')));

        $openid_msg = $this->openid_memento_service->getCurrentRequest();
        $oauth2_msg = $this->oauth2_memento_service->getCurrentAuthorizationRequest();
        if (!is_null($openid_msg) && $openid_msg->isValid() && OpenIdAuthenticationRequest::IsOpenIdAuthenticationRequest($openid_msg)) {
            //openid stuff
            $this->beforeFilter('openid.save.request');
            $this->beforeFilter('openid.needs.auth.request', array('only' => array('getConsent')));
            $this->login_strategy = new OpenIdLoginStrategy($openid_memento_service, $user_action_service, $auth_service);
            $this->consent_strategy = new OpenIdConsentStrategy($openid_memento_service, $auth_service, $server_configuration_service, $user_action_service);
        } else if (!is_null($oauth2_msg) && $oauth2_msg->isValid()) {
            $this->beforeFilter('oauth2.save.request');
            $this->beforeFilter('oauth2.needs.auth.request', array('only' => array('getConsent')));
            $this->login_strategy = new OAuth2LoginStrategy();
            $this->consent_strategy = new OAuth2ConsentStrategy($auth_service, $oauth2_memento_service, $scope_service, $client_service);
        } else {
            //default stuff
            $this->login_strategy = new DefaultLoginStrategy($user_action_service, $auth_service);
            $this->consent_strategy = null;
        }


        $this->beforeFilter('user.owns.client.policy:json', array('only' => array(
            'postAddAllowedScope',
            'getRegenerateClientSecret',
            'getDeleteClientAllowedUri',
            'postAddAllowedRedirectUri',
            'getRegisteredClientUris')));

         $this->beforeFilter('ajax', array('only' => array(
             'postAddAllowedScope',
             'getRegenerateClientSecret',
             'getDeleteClientAllowedUri',
             'postAddAllowedRedirectUri',
             'getRegisteredClientUris')));

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
                'username'      => $user->getFullName(),
                'show_email'    => $user->getShowProfileEmail(),
                'email'         => $user->getEmail(),
                'identifier'    => $user->getIdentifier(),
                'show_pic'      => $user->getShowProfilePic(),
                'pic'           => $user->getPic(),
                'another_user'  => $another_user,
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

    public function getEditRegisteredClient($id)
    {
        $client = $this->client_service->getClientByIdentifier($id);

        if (is_null($client)) {
            Log::warning(sprintf("invalid oauth2 client id %s", $id));
            return View::make("404");
        }

        $allowed_uris = $client->getClientRegisteredUris();
        $selected_scopes = $client->getClientScopes();
        $aux_scopes = array();
        foreach ($selected_scopes as $scope) {
            array_push($aux_scopes, $scope->id);
        }
        $scopes = $this->scope_service->getAvailableScopes();

        return View::make("edit-registered-client",
            array('client' => $client,
                'allowed_uris' => $allowed_uris,
                'selected_scopes' => $aux_scopes,
                'scopes' => $scopes
            ));
    }

    public function getRegisteredClientUris($id){
        try {
            $client = $this->client_service->getClientByIdentifier($id);
            $allowed_uris = $client->getClientRegisteredUris();

            $container = array();
            foreach($allowed_uris as $uri){
                array_push($container,array('id'=>$uri->id,'redirect_uri'=>$uri->uri));
            }

            return Response::json(array('status' => 'OK','allowed_uris'=>$container));
        } catch (Exception $ex) {
            Log::error($ex);
            return Response::json(array('status' => 'ERROR'));
        }
    }

    public function getDeleteRegisteredClient($id)
    {
        try {
            $this->client_service->deleteClientByIdentifier($id);
            return Redirect::back();
        } catch (Exception $ex) {
            Log::error($ex);
            return View::make("404");
        }
    }

    public function postAddRegisteredClient()
    {
        try {
            $input = Input::All();
            $user = $this->auth_service->getCurrentUser();
            // todo: check application unique name
            // Build the validation constraint set.
            $rules = array(
                'app_name' => 'required',
                'app_desc' => 'required',
                'app_type' => 'required',
            );

            // Create a new validator instance.
            $validator = Validator::make($input, $rules);

            if ($validator->passes()) {
                $app_name = trim($input['app_name']);
                $app_desc = trim($input['app_desc']);
                $app_type = $input['app_type'];
                $this->client_service->addClient($app_type, $user->getId(), $app_name, $app_desc, '');

                $clients = $user->getClients();

                $clients_response = array();

                foreach($clients as $client){
                    array_push($clients_response, array(
                        'id'          => $client->id,
                        'app_name'    => $client->app_name,
                        'client_type' => $client->getFriendlyClientType(),
                        'active'      => $client->active,
                        'locked'      => $client->locked,
                        'updated_at'  => $client->updated_at->format('Y-m-d H:i:s')
                    ));
                }
                return Response::json(array('status' => 'OK','clients'=> $clients_response));
            }

            throw new Exception("invalid param!");
        } catch (Exception $ex) {
            Log::error($ex);
            return Response::json(array('status' => 'ERROR'));
        }
    }

    public function postAddAllowedRedirectUri($id)
    {
        try {
            $input = Input::All();
            // Build the validation constraint set.
            $rules = array(
                'redirect_uri' => 'url',

            );
            $messages = array(
                'url' => 'You must give a valid url'
            );
            // Create a new validator instance.
            $validator = Validator::make($input, $rules, $messages);
            if ($validator->passes()) {
                $this->client_service->addClientAllowedUri($id, $input['redirect_uri']);
                return Response::json(array('status' => 'OK'));
            } else {
                return Response::json(array('status' => 'ERROR'));
            }
        }
        catch (AllowedClientUriAlreadyExistsException $ex1) {
            Log::error($ex1);
            return Response::json(array('status' => 'ERROR','msg'=>'Uri already exists!'));
        }
        catch (Exception $ex) {
            Log::error($ex);
            return Response::json(array('status' => 'ERROR','msg'=>'There was an error!'));
        }
    }

    public function getDeleteClientAllowedUri($id, $uri_id)
    {
        try {
            $this->client_service->deleteClientAllowedUri($id, $uri_id);
            return Response::json(array('status' => 'OK'));
        } catch (Exception $ex) {
            Log::error($ex);
            return Response::json(array('status' => 'ERROR'));
        }
    }

    public function getRegenerateClientSecret($id)
    {
        try {
            $new_secret = $this->client_service->regenerateClientSecret($id);
            return Response::json(array('status' => 'OK','new_secret'=>$new_secret));
        } catch (Exception $ex) {
            Log::error($ex);
            return Response::json(array('status' => 'ERROR'));
        }
    }

    public function postAddAllowedScope($id)
    {
        try {
            $input = Input::All();


            // Build the validation constraint set.
            $rules = array(
                'scope_id'        => 'required',
                'checked'   => 'required',
            );

            // Create a new validator instance.
            $validator = Validator::make($input, $rules);
            if ($validator->passes()) {
                $client_id = $id;
                $checked   = $input['checked'];
                $scope_id  = $input['scope_id'];
                if($checked){
                    $this->client_service->addClientScope($client_id,$scope_id);
                }
                else{
                    $this->client_service->deleteClientScope($client_id,$scope_id);
                }
                return Response::json(array('status' => 'OK'));
            }
        } catch (Exception $ex) {
            Log::error($ex);
            return Response::json(array('status' => 'ERROR'));
        }
    }

    public function postActivateClient(){
        try {
            $input = Input::All();

            $user = $this->auth_service->getCurrentUser();
            // Build the validation constraint set.
            $rules = array(
                'id'        => 'required',
                'active'   => 'required',
            );

            // Create a new validator instance.
            $validator = Validator::make($input, $rules);
            if ($validator->passes()) {

                $id      = $input['id'];
                $active  = $input['active'];

                $this->client_service->activateClient($id,$active,$user->getId());

                return Response::json(array('status' => 'OK'));
            }
        } catch (Exception $ex) {
            Log::error($ex);
            return Response::json(array('status' => 'ERROR'));
        }
    }
}
