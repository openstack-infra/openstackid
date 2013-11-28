<?php

namespace strategies;

use openid\exceptions\InvalidOpenIdMessageException;
use openid\exceptions\InvalidRequestContextException;
use openid\OpenIdProtocol;
use openid\services\IAuthService;
use openid\services\IMementoOpenIdRequestService;
use openid\services\IServerConfigurationService;
use services\IPHelper;
use services\IUserActionService;
use \Auth;
use \Redirect;
use \View;

class OpenIdConsentStrategy implements IConsentStrategy
{


    private $memento_service;
    private $auth_service;
    private $server_configuration_service;
    private $user_action_service;

    public function __construct(IMementoOpenIdRequestService $memento_service, IAuthService $auth_service, IServerConfigurationService $server_configuration_service, IUserActionService $user_action_service)
    {
        $this->memento_service = $memento_service;
        $this->auth_service = $auth_service;
        $this->server_configuration_service = $server_configuration_service;
        $this->user_action_service = $user_action_service;
    }

    public function getConsent()
    {
        $data = $this->getViewData();
        return View::make("consent", $data);
    }

    private function getViewData()
    {
        $context = Session::get('context');
        if (is_null($context))
            throw new InvalidRequestContextException();
        $partial_views = $context->getPartials();
        $data = array();
        $views = array();
        foreach ($partial_views as $partial) {
            $views[$partial->getName()] = View::make($partial->getName(), $partial->getData());
        }
        $request = $this->memento_service->getCurrentRequest();
        $user = $this->auth_service->getCurrentUser();
        $data['realm'] = $request->getParam(OpenIdProtocol::OpenIDProtocol_Realm);
        $data['openid_url'] = $this->server_configuration_service->getUserIdentityEndpointURL($user->getIdentifier());
        $data['views'] = $views;
        return $data;
    }

    public function postConsent($trust_action)
    {
        if (is_array($trust_action)) {
            $msg = $this->memento_service->getCurrentRequest();
            if (is_null($msg) || !$msg->isValid())
                throw new InvalidOpenIdMessageException();
            $this->user_action_service->addUserAction($this->auth_service->getCurrentUser(), IPHelper::getUserIp(), IUserActionService::ConsentAction, $msg->getParam(OpenIdProtocol::OpenIDProtocol_Realm));
            $this->auth_service->setUserAuthorizationResponse($trust_action[0]);
            return Redirect::action('OpenIdProviderController@op_endpoint');
        }
        return Redirect::action('UserController@getConsent');
    }
}