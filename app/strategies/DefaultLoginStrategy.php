<?php
namespace strategies;

use Auth;
use Redirect;
use services\IPHelper;
use services\IUserActionService;
use utils\services\IAuthService;
use View;

class DefaultLoginStrategy implements ILoginStrategy
{

    private $user_action_service;
    private $auth_service;

    public function __construct(IUserActionService $user_action_service,
                                IAuthService $auth_service)
    {
        $this->user_action_service = $user_action_service;
        $this->auth_service        = $auth_service;
    }

    public function  getLogin()
    {
        if (Auth::guest())
            return View::make("login");
        return Redirect::action("UserController@getProfile");
    }

    public function  postLogin()
    {
        $user = $this->auth_service->getCurrentUser();
        $identifier = $user->getIdentifier();
        $this->user_action_service->addUserAction($this->auth_service->getCurrentUser(), IPHelper::getUserIp(), IUserActionService::LoginAction);
        return Redirect::action("UserController@getIdentity", array("identifier" => $identifier));
    }

    public function  cancelLogin()
    {
        return Redirect::action("HomeController@index");
    }
}