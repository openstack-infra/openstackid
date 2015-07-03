<?php

namespace strategies;

use Auth;
use oauth2\factories\OAuth2AuthorizationRequestFactory;
use oauth2\OAuth2Message;
use oauth2\services\IMementoOAuth2SerializerService;
use oauth2\services\ISecurityContextService;
use Redirect;
use services\IUserActionService;
use utils\IPHelper;
use utils\services\IAuthService;
use View;
use Session;

/**
 * Class OAuth2LoginStrategy
 * @package strategies
 */
class OAuth2LoginStrategy implements ILoginStrategy
{

    /**
     * @var IMementoOAuth2SerializerService
     */
    private $memento_service;
    /**
     * @var IUserActionService
     */
    private $user_action_service;
    /**
     * @var IAuthService
     */
    private $auth_service;

    /**
     * @var ISecurityContextService
     */
    private $security_context_service;

    /**
     * @param IAuthService $auth_service
     * @param IMementoOAuth2SerializerService $memento_service
     * @param IUserActionService $user_action_service
     * @param ISecurityContextService $security_context_service
     */
    public function __construct
    (
        IAuthService $auth_service,
        IMementoOAuth2SerializerService $memento_service,
        IUserActionService $user_action_service,
        ISecurityContextService $security_context_service
    )
    {
        $this->memento_service          = $memento_service;
        $this->user_action_service      = $user_action_service;
        $this->auth_service             = $auth_service;
        $this->security_context_service = $security_context_service;
    }

    public function getLogin()
    {
        if (Auth::guest()) {
            $requested_user_id = $this->security_context_service->get()->getRequestedUserId();
            if(!is_null($requested_user_id))
            {
                Session::put('username', $this->auth_service->getUserByExternaldId($requested_user_id)->getEmail());
            }
            return View::make("login");
        } else {
            return Redirect::action("UserController@getProfile");
        }
    }

    public function postLogin()
    {
        $auth_request = OAuth2AuthorizationRequestFactory::getInstance()->build(
            OAuth2Message::buildFromMemento(
                $this->memento_service->load()
            )
        );

        $this->user_action_service->addUserAction($this->auth_service->getCurrentUser(), IPHelper::getUserIp(),
            IUserActionService::LoginAction, $auth_request->getRedirectUri());

        return Redirect::action("OAuth2ProviderController@authorize");
    }

    public function cancelLogin()
    {
        $this->auth_service->setUserAuthenticationResponse(IAuthService::AuthenticationResponse_Cancel);

        return Redirect::action("OAuth2ProviderController@authorize");
    }
}