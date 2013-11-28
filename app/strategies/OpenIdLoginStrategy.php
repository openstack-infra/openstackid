<?php

namespace strategies;

use openid\OpenIdProtocol;
use openid\requests\OpenIdAuthenticationRequest;
use openid\responses\OpenIdNonImmediateNegativeAssertion;
use openid\services\IMementoOpenIdRequestService;
use openid\strategies\OpenIdResponseStrategyFactoryMethod;
use services\IPHelper;
use services\IUserActionService;
use \Auth;
use \Redirect;
use \View;

class OpenIdLoginStrategy implements ILoginStrategy
{

    private $memento_service;
    private $user_action_service;
    private $auth_service;

    public function __construct(IMementoOpenIdRequestService $memento_service,
                                IUserActionService $user_action_service,
                                IAuthService $auth_service)
    {
        $this->memento_service     = $memento_service;
        $this->user_action_service = $user_action_service;
        $this->auth_service        = $auth_service;
    }

    public function getLogin()
    {
        if (Auth::guest()) {
            $msg = $this->memento_service->getCurrentRequest();
            if (is_null($msg) || !$msg->isValid() || !OpenIdAuthenticationRequest::IsOpenIdAuthenticationRequest($msg))
                return View::make("login");
            else {
                $auth_request = new OpenIdAuthenticationRequest($msg);
                $params = array('realm' => $auth_request->getRealm());

                if (!$auth_request->isIdentitySelectByOP()) {
                    $params['claimed_id'] = $auth_request->getClaimedId();
                    $params['identity'] = $auth_request->getIdentity();
                    $params['identity_select'] = false;
                } else {
                    $params['identity_select'] = true;
                }
                return View::make("login", $params);
            }
        } else {
            return Redirect::action("UserController@getProfile");
        }
    }

    public function  postLogin()
    {
        //go to authentication flow again
        $msg = $this->memento_service->getCurrentRequest();
        $this->user_action_service->addUserAction($this->auth_service->getCurrentUser(), IPHelper::getUserIp(), IUserActionService::LoginAction, $msg->getParam(OpenIdProtocol::OpenIDProtocol_Realm));
        return Redirect::action("OpenIdProviderController@op_endpoint");
    }

    public function  cancelLogin()
    {
       $msg = $this->memento_service->getCurrentRequest();
       $cancel_response = new OpenIdNonImmediateNegativeAssertion();
       $cancel_response->setReturnTo($msg->getParam(OpenIdProtocol::OpenIDProtocol_ReturnTo));
       $strategy = OpenIdResponseStrategyFactoryMethod::buildStrategy($cancel_response);
       return $strategy->handle($cancel_response);
    }
}