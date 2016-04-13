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
use OAuth2\OAuth2Message;
use OAuth2\Repositories\IClientRepository;
use OAuth2\Services\IApiScopeService;
use OAuth2\Services\IClientService;
use OAuth2\Services\IMementoOAuth2SerializerService;
use OAuth2\Factories\OAuth2AuthorizationRequestFactory;
use Utils\Services\IAuthService;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
/**
 * Class OAuth2ConsentStrategy
 * @package Strategies
 */
class OAuth2ConsentStrategy implements IConsentStrategy
{
    /**
     * @var IAuthService
     */
    private $auth_service;
    /**
     * @var IMementoOAuth2SerializerService
     */
    private $memento_service;
    /**
     * @var IApiScopeService
     */
    private $scope_service;
    /**
     * @var IClientRepository
     */
    private $client_repository;

    /**
     * @param IAuthService $auth_service
     * @param IMementoOAuth2SerializerService $memento_service
     * @param IApiScopeService $scope_service
     * @param IClientRepository $client_repository
     */
    public function __construct
    (
        IAuthService $auth_service,
        IMementoOAuth2SerializerService $memento_service,
        IApiScopeService $scope_service,
        IClientRepository $client_repository
    )
    {
        $this->auth_service      = $auth_service;
        $this->memento_service   = $memento_service;
        $this->scope_service     = $scope_service;
        $this->client_repository = $client_repository;
    }

    public function getConsent()
    {
        $auth_request = OAuth2AuthorizationRequestFactory::getInstance()->build
        (
            OAuth2Message::buildFromMemento
            (
                $this->memento_service->load()
            )
        );

        $client_id                = $auth_request->getClientId();
        $client                   = $this->client_repository->getClientById($client_id);
        $scopes                   = explode(' ',$auth_request->getScope());
        $requested_scopes         = $this->scope_service->getScopesByName($scopes);

        $data                     = array();
        $data['requested_scopes'] = $requested_scopes;
        $data['app_name']         = $client->getApplicationName();
        $data['redirect_to']      = $auth_request->getRedirectUri();
        $data['website']          = $client->getWebsite();
        $data['tos_uri']          = $client->getTermOfServiceUri();
        $data['policy_uri']       = $client->getPolicyUri();

        $app_logo                 = $client->getApplicationLogo();

        $data['app_logo']         = $app_logo;
        $data['app_description']  = $client->getApplicationDescription();
        $data['dev_info_email']   = $client->getDeveloperEmail();

        $response_strategy = DisplayResponseStrategyFactory::build($auth_request->getDisplay());

        return $response_strategy->getConsentResponse($data);

    }

    public function postConsent($trust_action)
    {
        $this->auth_service->setUserAuthorizationResponse($trust_action);
        return Redirect::action('OAuth2\OAuth2ProviderController@auth');
    }
}