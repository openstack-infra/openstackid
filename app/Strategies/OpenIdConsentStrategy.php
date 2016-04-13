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
use Illuminate\Support\Facades\Auth;
use OpenId\Exceptions\InvalidOpenIdMessageException;
use OpenId\Exceptions\InvalidRequestContextException;
use OpenId\OpenIdMessage;
use OpenId\OpenIdProtocol;
use OpenId\Services\IMementoOpenIdSerializerService;
use OpenId\Services\IServerConfigurationService;
use Utils\IPHelper;
use Services\IUserActionService;
use Utils\Services\IAuthService;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
/**
 * Class OpenIdConsentStrategy
 * @package Strategies
 */
final class OpenIdConsentStrategy implements IConsentStrategy
{

    /**
     * @var IMementoOpenIdSerializerService
     */
    private $memento_service;

    /**
     * @var IAuthService
     */
    private $auth_service;

    /**
     * @var IServerConfigurationService
     */
    private $server_configuration_service;

    /**
     * @var IUserActionService
     */
    private $user_action_service;

    /**
     * @param IMementoOpenIdSerializerService $memento_service
     * @param IAuthService $auth_service
     * @param IServerConfigurationService $server_configuration_service
     * @param IUserActionService $user_action_service
     */
    public function __construct(
        IMementoOpenIdSerializerService $memento_service,
        IAuthService $auth_service,
        IServerConfigurationService $server_configuration_service,
        IUserActionService $user_action_service
    )
    {
        $this->memento_service              = $memento_service;
        $this->auth_service                 = $auth_service;
        $this->server_configuration_service = $server_configuration_service;
        $this->user_action_service          = $user_action_service;
    }

    public function getConsent()
    {
        $data = $this->getViewData();
        return View::make("openid.consent", $data);
    }

    /**
     * @return array
     * @throws InvalidRequestContextException
     */
    private function getViewData()
    {
        $context = Session::get('openid.auth.context');

        if (is_null($context))
            throw new InvalidRequestContextException();

        $partial_views      = $context->getPartials();
        $data               = array();
        $request            = OpenIdMessage::buildFromMemento( $this->memento_service->load());
        $user               = $this->auth_service->getCurrentUser();
        $data['realm']      = $request->getParam(OpenIdProtocol::OpenIDProtocol_Realm);
        $data['openid_url'] = $this->server_configuration_service->getUserIdentityEndpointURL($user->getIdentifier());
        $data['views']      = $partial_views;
        return $data;
    }

    /**
     * @param $trust_action
     * @return mixed
     * @throws InvalidOpenIdMessageException
     */
    public function postConsent($trust_action)
    {
        if (is_array($trust_action)) {
            $msg =  OpenIdMessage::buildFromMemento( $this->memento_service->load());
            if (is_null($msg) || !$msg->isValid())
                throw new InvalidOpenIdMessageException();
            $this->user_action_service->addUserAction($this->auth_service->getCurrentUser(), IPHelper::getUserIp(), IUserActionService::ConsentAction, $msg->getParam(OpenIdProtocol::OpenIDProtocol_Realm));
            $this->auth_service->setUserAuthorizationResponse($trust_action[0]);
            Session::remove('openid.auth.context');
            Session::save();
            return Redirect::action('OpenIdProviderController@endpoint');
        }
        return Redirect::action('UserController@getConsent');
    }
}