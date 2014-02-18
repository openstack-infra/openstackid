<?php

namespace strategies;

use Auth;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use Redirect;
use View;
use services\IUserActionService;
use utils\services\IAuthService;
use utils\IPHelper;

class OAuth2LoginStrategy implements  ILoginStrategy{

	private $memento_service;
	private $user_action_service;
	private $auth_service;

	public function __construct(IAuthService $auth_service,
								IMementoOAuth2AuthenticationRequestService $memento_service,
	                            IUserActionService $user_action_service
	                            )
	{
		$this->memento_service     = $memento_service;
		$this->user_action_service = $user_action_service;
		$this->auth_service        = $auth_service;
	}

	public function getLogin()
    {
        if (Auth::guest()) {
            return View::make("login");
        } else {
            return Redirect::action("UserController@getProfile");
        }
    }

    public function postLogin()
    {
	    $auth_request = $this->memento_service->getCurrentAuthorizationRequest();
	    $this->user_action_service->addUserAction($this->auth_service->getCurrentUser(), IPHelper::getUserIp(), IUserActionService::LoginAction, $auth_request->getRedirectUri() );
        return Redirect::action("OAuth2ProviderController@authorize");
    }

    public function cancelLogin()
    {
		$this->auth_service->setUserAuthenticationResponse(IAuthService::AuthenticationResponse_Cancel);
        return Redirect::action("OAuth2ProviderController@authorize");
    }
}