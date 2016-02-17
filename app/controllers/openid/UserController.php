<?php

use oauth2\services\IApiScopeService;
use oauth2\services\IClientService;
use oauth2\services\IResourceServerService;
use oauth2\services\ITokenService;
use openid\requests\OpenIdAuthenticationRequest;
use openid\services\IMementoOpenIdSerializerService;
use openid\services\IServerConfigurationService;
use openid\services\ITrustedSitesService;
use openid\services\IUserService;
use services\IUserActionService;
use strategies\DefaultLoginStrategy;
use strategies\OAuth2ConsentStrategy;
use strategies\OAuth2LoginStrategy;
use strategies\OpenIdConsentStrategy;
use strategies\OpenIdLoginStrategy;
use utils\IPHelper;
use utils\services\IAuthService;
use utils\services\IServerConfigurationService as IUtilsServerConfigurationService;
use oauth2\services\IMementoOAuth2SerializerService;
use oauth2\services\ISecurityContextService;

/**
 * Class UserController
 */
class UserController extends OpenIdController
{

    /**
     * @var IMementoOpenIdSerializerService
     */
    private $openid_memento_service;
    /**
     * @var IMementoOAuth2SerializerService
     */
    private $oauth2_memento_service;
    /**
     * @var IAuthService
     */
    private $auth_service;
    /**
     * @var IServerConfigurationService
     */
    private $server_configuration_service;
    /**
     * @var DiscoveryController
     */
    private $discovery;
    /**
     * @var IUserService
     */
    private $user_service;
    /**
     * @var IUserActionService
     */
    private $user_action_service;
    /**
     * @var DefaultLoginStrategy
     */
    private $login_strategy;
    /**
     * @var null
     */
    private $consent_strategy;
    /**
     * @var IClientService
     */
    private $client_service;
    /**
     * @var IApiScopeService
     */
    private $scope_service;
    /**
     * @var ITokenService
     */
    private $token_service;
    /**
     * @var IResourceServerService
     */
    private $resource_server_service;
    /**
     * @var IUtilsServerConfigurationService
     */
    private $utils_configuration_service;

    /**
     * @param IMementoOpenIdSerializerService $openid_memento_service
     * @param IMementoOAuth2SerializerService $oauth2_memento_service
     * @param IAuthService $auth_service
     * @param IServerConfigurationService $server_configuration_service
     * @param ITrustedSitesService $trusted_sites_service
     * @param DiscoveryController $discovery
     * @param IUserService $user_service
     * @param IUserActionService $user_action_service
     * @param IClientService $client_service
     * @param IApiScopeService $scope_service
     * @param ITokenService $token_service
     * @param IResourceServerService $resource_server_service
     * @param IUtilsServerConfigurationService $utils_configuration_service
     */
    public function __construct
    (
        IMementoOpenIdSerializerService $openid_memento_service,
        IMementoOAuth2SerializerService $oauth2_memento_service,
        IAuthService $auth_service,
        IServerConfigurationService $server_configuration_service,
        ITrustedSitesService $trusted_sites_service,
        DiscoveryController $discovery,
        IUserService $user_service,
        IUserActionService $user_action_service,
        IClientService $client_service,
        IApiScopeService $scope_service,
        ITokenService $token_service,
        IResourceServerService $resource_server_service,
        IUtilsServerConfigurationService $utils_configuration_service,
        ISecurityContextService $security_context_service
    )
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
        $this->token_service = $token_service;
        $this->resource_server_service = $resource_server_service;
        $this->utils_configuration_service = $utils_configuration_service;
        //filters
        $this->beforeFilter('csrf', array('only' => array('postLogin', 'postConsent')));

        if ($this->openid_memento_service->exists())
        {
            //openid stuff
            $this->login_strategy   = new OpenIdLoginStrategy
            (
                $openid_memento_service,
                $user_action_service,
                $auth_service
            );

            $this->consent_strategy = new OpenIdConsentStrategy
            (
                $openid_memento_service,
                $auth_service,
                $server_configuration_service,
                $user_action_service
            );

        }
        else if ($this->oauth2_memento_service->exists())
        {

                $this->login_strategy = new OAuth2LoginStrategy
                (
                    $auth_service,
                    $oauth2_memento_service,
                    $user_action_service,
                    $security_context_service
                );

                $this->consent_strategy = new OAuth2ConsentStrategy
                (
                    $auth_service,
                    $oauth2_memento_service,
                    $scope_service,
                    $client_service
                );
        }
        else
        {
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
        try
        {
            $max_login_attempts_2_show_captcha = $this->server_configuration_service->getConfigValue("MaxFailed.LoginAttempts.2ShowCaptcha");
            $data = Input::all();
            $login_attempts = intval(Input::get('login_attempts'));
            // Build the validation constraint set.
            $rules = array
            (
                'username' => 'required|email',
                'password' => 'required',
            );
            if ($login_attempts >= $max_login_attempts_2_show_captcha)
            {
                $rules['g-recaptcha-response'] = 'required|recaptcha';
            }
            // Create a new validator instance.
            $validator = Validator::make($data, $rules);

            if ($validator->passes())
            {
                $username = Input::get("username");
                $password = Input::get("password");
                $remember = Input::get("remember");

                $remember = !is_null($remember);
                if ($this->auth_service->login($username, $password, $remember))
                {
                    return $this->login_strategy->postLogin();
                }
                //failed login attempt...
                $user = $this->auth_service->getUserByUsername($username);
                if ($user)
                {
                    $login_attempts = $user->login_failed_attempt;
                }

                return $this->login_strategy->errorLogin
                (
                    array
                    (
                        'max_login_attempts_2_show_captcha' => $max_login_attempts_2_show_captcha,
                        'login_attempts'                    => $login_attempts,
                        'username'                          => $username,
                        'error_message'                     => '"We\'re sorry, your username or password does not match an existing record."'
                    )
                );
            }

            return Redirect::action('UserController@getLogin')
                ->withErrors($validator);
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return Redirect::action('UserController@getLogin');
        }
    }

    public function getConsent()
    {
        if (is_null($this->consent_strategy))
        {
            return View::make("404");
        }

        return $this->consent_strategy->getConsent();
    }

    public function postConsent()
    {
        try
        {
            $data  = Input::all();
            $rules = array
            (
                'trust' => 'required|oauth2_trust_response',
            );
            // Create a new validator instance.
            $validator = Validator::make($data, $rules);
            if ($validator->passes())
            {
                return $this->consent_strategy->postConsent(input::get("trust"));
            }
            return Redirect::action('UserController@getConsent')->withErrors($validator);
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return Redirect::action('UserController@getConsent');
        }
    }

    public function getIdentity($identifier)
    {
        try
        {
            $user = $this->auth_service->getUserByOpenId($identifier);
            if (is_null($user))
            {
                return View::make("404");
            }

            if ($this->isDiscoveryRequest())
            {
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
            if ($current_user && $current_user->getIdentifier() != $user->getIdentifier())
            {
                $another_user = true;
            }

            $assets_url = $this->utils_configuration_service->getConfigValue("Assets.Url");
            $pic_url = $user->getPic();
            $pic_url = str_contains($pic_url, 'http') ? $pic_url : $assets_url . $pic_url;

            $params = array
            (
                'show_fullname' => $user->getShowProfileFullName(),
                'username' => $user->getFullName(),
                'show_email' => $user->getShowProfileEmail(),
                'email' => $user->getEmail(),
                'identifier' => $user->getIdentifier(),
                'show_pic' => $user->getShowProfilePic(),
                'pic' => $pic_url,
                'another_user' => $another_user,
            );

            return View::make("identity", $params);
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return View::make("404");
        }
    }

    public function logout()
    {
        $this->user_action_service->addUserAction
        (
            $this->auth_service->getCurrentUser(),
            IPHelper::getUserIp(),
            IUserActionService::LogoutAction
        );
        $this->auth_service->logout();
        Session::flush();
        Session::regenerate();
        return Redirect::action("UserController@getLogin");
    }

    public function getProfile()
    {
        $user    = $this->auth_service->getCurrentUser();
        $sites   = $user->getTrustedSites();
        $actions = $user->getActions();

        return View::make("profile", array
        (
            "username"             => $user->getFullName(),
            "user_id"              => $user->getId(),
            "is_oauth2_admin"      => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            "use_system_scopes"    => $user->canUseSystemScopes(),
            "openid_url"           => $this->server_configuration_service->getUserIdentityEndpointURL($user->getIdentifier()),
            "identifier "          => $user->getIdentifier(),
            "sites"                => $sites,
            "show_pic"             => $user->getShowProfilePic(),
            "show_full_name"       => $user->getShowProfileFullName(),
            "show_email"           => $user->getShowProfileEmail(),
            'actions'              => $actions,
        ));
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

    public function deleteTrustedSite($id)
    {
        $this->trusted_sites_service->delTrustedSite($id);
        return Redirect::action("UserController@getProfile");
    }

}