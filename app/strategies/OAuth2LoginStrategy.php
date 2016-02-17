<?php

namespace strategies;

use Auth;
use oauth2\factories\OAuth2AuthorizationRequestFactory;
use oauth2\OAuth2Message;
use oauth2\OAuth2Protocol;
use oauth2\services\IMementoOAuth2SerializerService;
use oauth2\services\ISecurityContextService;
use Redirect;
use services\IUserActionService;
use utils\IPHelper;
use utils\services\IAuthService;
use View;
use Session;
use Response;
use URL;

/**
 * Class OAuth2LoginStrategy
 * @package strategies
 */
class OAuth2LoginStrategy extends DefaultLoginStrategy
{

    /**
     * @var IMementoOAuth2SerializerService
     */
    private $memento_service;

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
        parent::__construct($user_action_service, $auth_service);
        $this->memento_service          = $memento_service;
        $this->security_context_service = $security_context_service;
    }

    public function getLogin()
    {
        if (Auth::guest())
        {
            $requested_user_id = $this->security_context_service->get()->getRequestedUserId();
            if(!is_null($requested_user_id))
            {
                Session::put('username', $this->auth_service->getUserById($requested_user_id)->getEmail());
                Session::save();
            }
            $auth_request = OAuth2AuthorizationRequestFactory::getInstance()->build(
                OAuth2Message::buildFromMemento(
                    $this->memento_service->load()
                )
            );
            $display = $auth_request->getDisplay();

            if($display === OAuth2Protocol::OAuth2Protocol_Display_Page)
                return Response::view("login", array(), 200);

            if($display === OAuth2Protocol::OAuth2Protocol_Display_Touch)
            {
                $data = array
                (
                    'required_params'   => array('username','password', '_token'),
                    'optional_params'   => array('remember'),
                    'required_params_valid_values' => array
                    (
                        '_token' => csrf_token()
                    ),
                    'url'               => URL::action('UserController@postLogin'),
                    'method'            => 'POST',
                );
                if(!is_null($requested_user_id))
                {
                    $data['required_params_valid_values']['username'] = $this->auth_service->getUserById($requested_user_id)->getEmail();
                }
                return Response::json($data, 412);
            }

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

    /**
     * @param array $params
     * @return mixed
     */
    public function errorLogin(array $params)
    {
        $auth_request = OAuth2AuthorizationRequestFactory::getInstance()->build(
            OAuth2Message::buildFromMemento(
                $this->memento_service->load()
            )
        );
        $display = $auth_request->getDisplay();

        if($display === OAuth2Protocol::OAuth2Protocol_Display_Page)
            return parent::errorLogin($params);

        if($display === OAuth2Protocol::OAuth2Protocol_Display_Touch)
        {
            return Response::json($params, 412);
        }
    }
}