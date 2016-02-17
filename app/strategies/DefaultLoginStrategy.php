<?php

namespace strategies;

use Auth;
use Redirect;
use utils\IPHelper;
use services\IUserActionService;
use utils\services\IAuthService;
use View;
use URL;

/**
 * Class DefaultLoginStrategy
 * @package strategies
 */
class DefaultLoginStrategy implements ILoginStrategy
{

    /**
     * @var IUserActionService
     */
    protected $user_action_service;
    /**
     * @var IAuthService
     */
    protected $auth_service;

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
        $default_url = URL::action("UserController@getIdentity", array("identifier" => $identifier));
        return Redirect::intended($default_url);

    }

    public function  cancelLogin()
    {
        return Redirect::action("HomeController@index");
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function errorLogin(array $params)
    {
        return Redirect::action('UserController@getLogin')
            ->with('max_login_attempts_2_show_captcha', $params['max_login_attempts_2_show_captcha'])
            ->with('login_attempts', $params['login_attempts'])
            ->with('username', $params['username'])
            ->with('flash_notice', $params['error_message']);
    }
}