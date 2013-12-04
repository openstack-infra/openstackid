<?php

namespace strategies;
use  utils\services\IAuthService;
use Redirect;
use View;

/**
 * Class OAuth2ConsentStrategy
 * @package strategies
 */
class OAuth2ConsentStrategy implements  IConsentStrategy {

    private $auth_service;

    public function __construct(IAuthService $auth_service)
    {
        $this->auth_service = $auth_service;
    }

    public function getConsent()
    {
        return View::make("oauth2.consent");
    }

    public function postConsent($trust_action)
    {
        $this->auth_service->setUserAuthorizationResponse($trust_action[0]);
        return Redirect::action('OAuth2ProviderController@authorize');
    }
}