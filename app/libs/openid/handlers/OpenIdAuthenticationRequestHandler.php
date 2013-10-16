<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:43 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\handlers;


use openid\OpenIdMessage;
use openid\requests\OpenIdAuthenticationRequest;
use openid\services\IAuthService;
use openid\services\IMementoOpenIdRequestService;
/**
 * Class OpenIdAuthenticationRequestHandler
 * @package openid\handlers
 */
class OpenIdAuthenticationRequestHandler extends OpenIdMessageHandler
{
    private $authService;
    private $mementoRequestService;
    private $auth_strategy;

    public function __construct(IAuthService $authService,
                                IMementoOpenIdRequestService $mementoRequestService,
                                IOpenIdAuthenticationStrategy $auth_strategy, $successor)
    {
        parent::__construct($successor);
        $this->authService           = $authService;
        $this->mementoRequestService = $mementoRequestService;
        $this->auth_strategy         = $auth_strategy;
    }

    protected function InternalHandle(OpenIdMessage $message)
    {
        $request = new OpenIdAuthenticationRequest($message);
        //validate request?
        if(!$this->authService->isUserLogged()){
            //do login process
            $this->mementoRequestService->saveCurrentRequest();
            return $this->auth_strategy->doLogin($request);
        }
        else {
            //user already logged
            $currentUser = $this->authService->getCurrentUser();
            if($this->authService->isUserAuthorized()){
                // make assertion about identity
                // factory response and return
            }
            else{
                //do consent process
                $this->mementoRequestService->saveCurrentRequest();
                $this->auth_strategy->doConsent($request);
            }
        }
    }

    protected function CanHandle(OpenIdMessage $message)
    {
        return OpenIdAuthenticationRequest::IsOpenIdAuthenticationRequest($message);
    }
}