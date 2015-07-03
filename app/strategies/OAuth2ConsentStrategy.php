<?php

namespace strategies;
use oauth2\OAuth2Message;
use oauth2\services\IApiScopeService;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2SerializerService;
use  utils\services\IAuthService;
use Redirect;
use View;
use oauth2\factories\OAuth2AuthorizationRequestFactory;

/**
 * Class OAuth2ConsentStrategy
 * @package strategies
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
     * @var IClientService
     */
    private $client_service;

    /**
     * @param IAuthService $auth_service
     * @param IMementoOAuth2SerializerService $memento_service
     * @param IApiScopeService $scope_service
     * @param IClientService $client_service
     */
    public function __construct
    (
        IAuthService $auth_service,
        IMementoOAuth2SerializerService $memento_service,
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
        $auth_request = OAuth2AuthorizationRequestFactory::getInstance()->build
        (
            OAuth2Message::buildFromMemento
            (
                $this->memento_service->load()
            )
        );

        $client_id                = $auth_request->getClientId();
        $client                   = $this->client_service->getClientById($client_id);
        $scopes                   = explode(' ',$auth_request->getScope());
        $requested_scopes         = $this->scope_service->getScopesByName($scopes);

        $data = array();
        $data['requested_scopes'] = $requested_scopes;
        $data['app_name']         = $client->getApplicationName();
        $data['redirect_to']      = $auth_request->getRedirectUri();
        $data['website']          = $client->getWebsite();

        $app_logo                 = $client->getApplicationLogo();

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