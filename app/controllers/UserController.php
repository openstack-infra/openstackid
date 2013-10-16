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

    public function getLogin(){

        return View::make("login");
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
                return Redirect::to('/accounts/user/consent');
            }
            return Redirect::action('UserController@getLogin')->with('flash_notice', 'Authentication Failed!');
        }
        return Redirect::action('UserController@getLogin')->withErrors($validator);
    }

    public function getConsent(){
        return View::make("consent")->with("realm","test");
    }

    public function postConsent(){

        return Redirect::to('/accounts/openid/v2');
    }
}