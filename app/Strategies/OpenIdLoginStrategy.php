<?php namespace Strategies;
/**
 * Copyright 2015 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use OpenId\OpenIdMessage;
use OpenId\OpenIdProtocol;
use OpenId\Requests\OpenIdAuthenticationRequest;
use OpenId\Services\IMementoOpenIdSerializerService;
use Services\IUserActionService;
use Utils\IPHelper;
use Utils\Services\IAuthService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
/**
 * Class OpenIdLoginStrategy
 * @package Strategies
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