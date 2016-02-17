<?php

namespace strategies;

use Auth;
use openid\OpenIdMessage;
use openid\OpenIdProtocol;
use openid\requests\OpenIdAuthenticationRequest;
use openid\services\IMementoOpenIdSerializerService;
use Redirect;
use services\IUserActionService;
use utils\IPHelper;
use utils\services\IAuthService;
use View;

/**
 * Class OpenIdLoginStrategy
 * @package strategies
 */
final class OpenIdLoginStrategy extends DefaultLoginStrategy
{

    /**
     * @var IMementoOpenIdSerializerService
     */
    private $memento_service;

    /**
     * @param IMementoOpenIdSerializerService $memento_service
     * @param IUserActionService $user_action_service
     * @param IAuthService $auth_service
     */
    public function __construct(
        IMementoOpenIdSerializerService $memento_service,
        IUserActionService $user_action_service,
        IAuthService $auth_service
    ) {
        $this->memento_service = $memento_service;

        parent::__construct($user_action_service, $auth_service);
    }

    public function getLogin()
    {
        if (Auth::guest()) {
            $msg = OpenIdMessage::buildFromMemento($this->memento_service->load());
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
        } else {
            return Redirect::action("UserController@getProfile");
        }
    }

    public function  postLogin()
    {
        //go to authentication flow again
        $msg = OpenIdMessage::buildFromMemento($this->memento_service->load());
        $this->user_action_service->addUserAction($this->auth_service->getCurrentUser(), IPHelper::getUserIp(),
            IUserActionService::LoginAction, $msg->getParam(OpenIdProtocol::OpenIDProtocol_Realm));

        return Redirect::action("OpenIdProviderController@endpoint");
    }

    public function  cancelLogin()
    {
        $this->auth_service->setUserAuthenticationResponse(IAuthService::AuthenticationResponse_Cancel);

        return Redirect::action("OpenIdProviderController@endpoint");
    }
}