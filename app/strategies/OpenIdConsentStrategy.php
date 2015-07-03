<?php

namespace strategies;

use Auth;
use openid\exceptions\InvalidOpenIdMessageException;
use openid\exceptions\InvalidRequestContextException;
use openid\OpenIdMessage;
use openid\OpenIdProtocol;
use openid\services\IMementoOpenIdSerializerService;
use openid\services\IServerConfigurationService;
use Redirect;
use utils\IPHelper;
use services\IUserActionService;
use Session;
use utils\services\IAuthService;
use View;

class OpenIdConsentStrategy implements IConsentStrategy
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

    private function getViewData()
    {
        $context = Session::get('context');
        if (is_null($context))
            throw new InvalidRequestContextException();
        $partial_views = $context->getPartials();
        $data               = array();
        $request            = OpenIdMessage::buildFromMemento( $this->memento_service->load());
        $user               = $this->auth_service->getCurrentUser();
        $data['realm']      = $request->getParam(OpenIdProtocol::OpenIDProtocol_Realm);
        $data['openid_url'] = $this->server_configuration_service->getUserIdentityEndpointURL($user->getIdentifier());
        $data['views']      = $partial_views;
        return $data;
    }

    public function postConsent($trust_action)
    {
        if (is_array($trust_action)) {
            $msg =  OpenIdMessage::buildFromMemento( $this->memento_service->load());
            if (is_null($msg) || !$msg->isValid())
                throw new InvalidOpenIdMessageException();
            $this->user_action_service->addUserAction($this->auth_service->getCurrentUser(), IPHelper::getUserIp(), IUserActionService::ConsentAction, $msg->getParam(OpenIdProtocol::OpenIDProtocol_Realm));
            $this->auth_service->setUserAuthorizationResponse($trust_action[0]);
            return Redirect::action('OpenIdProviderController@endpoint');
        }
        return Redirect::action('UserController@getConsent');
    }
}