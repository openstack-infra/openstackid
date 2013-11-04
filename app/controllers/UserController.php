<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 3:21 PM
 * To change this template use File | Settings | File Templates.
 */

use openid\services\IMementoOpenIdRequestService;
use openid\services\IAuthService;
use openid\requests\OpenIdAuthenticationRequest;
use openid\exceptions\InvalidRequestContextException;
use openid\XRDS\XRDSDocumentBuilder;
use openid\services\IServerConfigurationService;
use openid\services\ITrustedSitesService;
use \openid\OpenIdProtocol;
use \openid\services\IUserService;
use openid\responses\OpenIdNonImmediateNegativeAssertion;
use openid\strategies\OpenIdResponseStrategyFactoryMethod;

class UserController extends BaseController{

    private $memento_service;
    private $auth_service;
    private $server_configuration_service;
    private $discovery;
    private $user_service;


    public function __construct(IMementoOpenIdRequestService $memento_service,
                                IAuthService $auth_service,
                                IServerConfigurationService $server_configuration_service,
                                ITrustedSitesService $trusted_sites_service,
                                DiscoveryController $discovery,
                                IUserService $user_service){
        $this->memento_service              = $memento_service;
        $this->auth_service                 = $auth_service;
        $this->server_configuration_service = $server_configuration_service;
        $this->trusted_sites_service        = $trusted_sites_service;
        $this->discovery                    = $discovery;
        $this->user_service                 = $user_service;
        //filters
        $this->beforeFilter('csrf',array('only' => array('postLogin', 'postConsent')));
        $this->beforeFilter('openid.save.request');
        $this->beforeFilter('openid.needs.auth.request',array('only' => array('getConsent')));
    }

    private function getViewData(){
        $context = Session::get('context');
        if(is_null($context))
            throw new InvalidRequestContextException();
        $partial_views = $context->getPartials();
        $data  = array();
        $views = array();
        foreach($partial_views as $partial){
            $views[$partial->getName()] = View::make($partial->getName(),$partial->getData());
        }
        $request              = $this->memento_service->getCurrentRequest();
        $user                 = $this->auth_service->getCurrentUser();
        $data['realm']        = $request->getParam(OpenIdProtocol::OpenIDProtocol_Realm);
        $data['openid_url']   = $this->server_configuration_service->getUserIdentityEndpointURL($user->getIdentifier());
        $data['views']        = $views;
        return $data;
    }

    public function getLogin(){
        if(Auth::guest())
            return View::make("login");
        else{
            return Redirect::action("UserController@getProfile");
        }
    }


    public function cancelLogin(){
        $msg = $this->memento_service->getCurrentRequest();
        if (!is_null($msg) && $msg->IsValid()){
            $cancel_response = new OpenIdNonImmediateNegativeAssertion();
            $cancel_response->setReturnTo($msg->getParam(OpenIdProtocol::OpenIDProtocol_ReturnTo));
            $strategy = OpenIdResponseStrategyFactoryMethod::buildStrategy($cancel_response);
            return $strategy->handle($cancel_response);
        }
        else{
            return Redirect::action("HomeController@index");
        }
    }

    public function postLogin(){
        $max_login_attempts_2_show_captcha = $this->server_configuration_service->getMaxFailedLoginAttempts2ShowCaptcha();
        $data = Input::all();
        $login_attempts =  intval(Input::get('login_attempts'));
        // Build the validation constraint set.
        $rules = array(
            'username' => 'required|email',
            'password' => 'required',
        );
        if($login_attempts>=$max_login_attempts_2_show_captcha){
            $rules['recaptcha_response_field'] = 'required|recaptcha';
        }
        // Create a new validator instance.
        $validator = Validator::make($data, $rules);


        if ($validator->passes()) {

            $username = Input::get("username");
            $password = Input::get("password");
            $remember = Input::get("remember");
            if(is_null($remember))
                $remember=false;
            else
                $remember=true;
            if($this->auth_service->Login($username,$password,$remember)){
                $msg = $this->memento_service->getCurrentRequest();
                if (!is_null($msg) && $msg->IsValid()){
                    //go to authentication flow again
                    return Redirect::action("OpenIdProviderController@op_endpoint");
                }
                else{
                    $user = $this->auth_service->getCurrentUser();
                    $identifier = $user->getIdentifier();
                    return Redirect::action("UserController@getIdentity",array("identifier"=> $identifier));
                }
            }
            $user = $this->auth_service->getUserByUsername($username);
            if($user){
                $login_attempts = $user->login_failed_attempt;
            }
            return Redirect::action('UserController@getLogin')->with('max_login_attempts_2_show_captcha',$max_login_attempts_2_show_captcha)->with('login_attempts',$login_attempts)->with('flash_notice', 'Authentication Failed!');
        }
        return Redirect::action('UserController@getLogin')->withErrors($validator);
    }

    public function getConsent(){
        $data = $this->getViewData();
        return View::make("consent",$data);
    }

    public function postConsent(){
        $trust_action = input::get("trust");
        if(!is_null($trust_action) && is_array($trust_action)){
            $this->auth_service->setUserAuthorizationResponse($trust_action[0]);
            return Redirect::action('OpenIdProviderController@op_endpoint');
        }
    }

    public function getIdentity($identifier){

        $user         = $this->auth_service->getUserByOpenId($identifier);
        if(is_null($user))
            return View::make("404");
        //This field contains a semicolon-separated list of representation schemes
        //which will be accepted in the response to this request.
        $accept = Request::header('Accept');
        $accept_values = explode(",",$accept);
        if(in_array(XRDSDocumentBuilder::ContentType,$accept_values)){
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
        if($current_user && $current_user->getIdentifier()!=$user->getIdentifier()){
            $another_user  = true;
        }
        $params = array(
            'show_fullname'=> $user->getShowProfileFullName(),
            'username'     => $user->getFullName(),
            'show_email'   => $user->getShowProfileEmail(),
            'email'        => $user->getEmail(),
            'identifier'   => $user->getIdentifier(),
            'show_pic'     => $user->getShowProfilePic(),
            'pic'          => $user->getPic(),
            'another_user' => $another_user,
        );
        return View::make("identity",$params);
    }

    public function logout()
    {
        Auth::logout();
        return Redirect::action("UserController@getLogin");
    }

    public function getProfile(){
        $user  = $this->auth_service->getCurrentUser();
        $sites = $this->trusted_sites_service->getAllTrustedSitesByUser($user);

        return View::make("profile",array(
            "username"       => $user->getFullName(),
            "openid_url"     => $this->server_configuration_service->getUserIdentityEndpointURL($user->getIdentifier()),
            "identifier "    => $user->getIdentifier(),
            "sites"          => $sites,
            "show_pic"       => $user->getShowProfilePic(),
            "show_full_name" => $user->getShowProfileFullName(),
            "show_email"     => $user->getShowProfileEmail(),
        ));
    }

    public function get_deleteTrustedSite($id){
        $this->trusted_sites_service->delTrustedSite($id);
        return Redirect::action("UserController@getProfile");
    }

    public function postUserProfileOptions(){
        $show_full_name = Input::get("show_full_name");
        $show_email     = Input::get("show_email");
        $show_pic       = Input::get("show_pic");
        $user           = $this->auth_service->getCurrentUser();

        $this->user_service->saveProfileInfo($user->getId(),$show_pic,$show_full_name,$show_email);
        return Redirect::action("UserController@getProfile");
    }
}
