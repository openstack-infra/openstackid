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
class UserController extends BaseController{

    private $memento_service;
    private $auth_service;

    public function __construct(IMementoOpenIdRequestService $memento_service, IAuthService $auth_service){
        $this->memento_service = $memento_service;
        $this->auth_service = $auth_service;
        //filters
        $this->beforeFilter('csrf',array('only' => array('postLogin', 'postConsent')));
        $this->beforeFilter('openid.save.request');
        $this->beforeFilter('openid.needs.auth.request',array('only' => array('getLogin', 'getConsent')));
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
        $data["views"]=$views;
        return $data;
    }

    public function getLogin(){
        $data = $this->getViewData();
        return View::make("login",$data);
    }

    public function postLogin(){
        $data = Input::all();
        // Build the validation constraint set.
        $rules = array(
            'username' => 'required',
            'password' => 'required'
        );
        // Create a new validator instance.
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $username = Input::get("username");
            $password = Input::get("password");
            if($this->auth_service->Login($username,$password)){
                //go to authentication flow again
                return Redirect::action("OpenIdProviderController@op_endpoint");
            }
            return Redirect::action('UserController@getLogin')->with('flash_notice', 'Authentication Failed!');
        }
        return Redirect::action('UserController@getLogin')->withErrors($validator);
    }

    public function getConsent(){
        $data = $this->getViewData();
        $data["realm"] ="test";
        return View::make("consent",$data);
    }

    public function postConsent(){
        $trust_action = input::get("trust");
        if(!is_null($trust_action) && is_array($trust_action)){
            $this->auth_service->setUserAuthorizationResponse($trust_action[0]);
            return Redirect::to('/accounts/openid/v2');
        }
    }
}