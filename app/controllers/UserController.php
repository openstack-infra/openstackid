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
class UserController extends BaseController{

    private $memento_service;
    private $auth_service;
    private $server_configuration_service;
    private $discovery;


    public function __construct(IMementoOpenIdRequestService $memento_service,
                                IAuthService $auth_service,
                                IServerConfigurationService $server_configuration_service,
                                ITrustedSitesService $trusted_sites_service,
                                DiscoveryController $discovery){
        $this->memento_service = $memento_service;
        $this->auth_service = $auth_service;
        $this->server_configuration_service = $server_configuration_service;
        $this->trusted_sites_service=$trusted_sites_service;
        $this->discovery = $discovery;
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
        $request         = $this->memento_service->getCurrentRequest();
        $user            = $this->auth_service->getCurrentUser();
        $data['realm']   = $request->getParam(OpenIdProtocol::OpenIDProtocol_Realm);
        $data['openid']  = $user->getIdentifier();
        $data['views']   = $views;
        return $data;
    }

    public function getLogin(){
        if(Auth::guest())
            return View::make("login");
        else{
            return Redirect::action("UserController@getProfile");
        }
    }

    public function postLogin(){
        $data = Input::all();
        // Build the validation constraint set.
        $rules = array(
            'username' => 'required|email',
            'password' => 'required'
        );
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
                    return Redirect::action("UserController@getIdentity",array("identifier"=> $user->getIdentifier()));
                }
            }
            return Redirect::action('UserController@getLogin')->with('flash_notice', 'Authentication Failed!');
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
            return Redirect::to('/accounts/openid/v2');
        }
    }

    public function getIdentity($identifier){

        $user = $this->auth_service->getUserByOpenId($identifier);
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

        if(Auth::check()){
            return View::make("identity")->with('username',$user->getFullName())->with( "identifier",$user->getIdentifier());
        }
        return View::make("identity");
    }

    public function logout()
    {
        Auth::logout();
        return Redirect::action("UserController@getLogin");
    }

    public function getProfile(){
        $user = $this->auth_service->getCurrentUser();
        $sites = $this->trusted_sites_service->getAllTrustedSitesByUser($user);
        return View::make("profile",array(
            "username"=> $user->getFullName(),
            "openid_url"=>$this->server_configuration_service->getUserIdentityEndpointURL($user->getIdentifier()),
            "identifier"=>$user->getIdentifier(),
            "sites"=>$sites
        ));
    }

    public function get_deleteTrustedSite($id){
        $this->trusted_sites_service->delTrustedSite($id);
        return Response::json(array('success' => true));
    }
}