<?php

namespace strategies;
use oauth2\services\IApiScopeService;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use  utils\services\IAuthService;
use Redirect;
use View;

/**
 * Class OAuth2ConsentStrategy
 * @package strategies
 */

class OAuth2ConsentStrategy implements  IConsentStrategy {

    private $auth_service;
    private $memento_service;
    private $scope_service;
    private $client_service;

    public function __construct(IAuthService $auth_service,
                                IMementoOAuth2AuthenticationRequestService $memento_service,
                                IApiScopeService $scope_service,
                                IClientService $client_service
                                )
    {
        $this->auth_service    = $auth_service;
        $this->memento_service = $memento_service;
        $this->scope_service   = $scope_service;
        $this->client_service  = $client_service;
    }

    public function getConsent()
    {
        $request = $this->memento_service->getCurrentAuthorizationRequest();
        $client_id = $request->getClientId();
        $client    = $this->client_service->getClientById($client_id);
        $scopes    = explode(' ',$request->getScope());
        $requested_scopes = $this->scope_service->getScopesByName($scopes);
        $data = array();
        $data['requested_scopes'] = $requested_scopes;
        $data['app_name']         = $client->getApplicationName();
        $data['redirect_to']      = $request->getRedirectUri();

        $app_logo                 = $client->getApplicationLogo();
        if(is_null($app_logo) || empty($app_logo))
            $app_logo = asset('img/oauth2.default.logo.png');
        $data['app_logo']         = $app_logo;
        $data['app_description']  = $client->getApplicationDescription();
        $data['dev_info_email']   = $client->getDeveloperEmail();

        return View::make("oauth2.consent",$data);
    }

    public function postConsent($trust_action)
    {
        $this->auth_service->setUserAuthorizationResponse($trust_action);
        return Redirect::action('OAuth2ProviderController@authorize');
    }
}