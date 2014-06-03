<?php

namespace openid\extensions;

use auth\exceptions\AuthenticationException;
use auth\IAuthenticationExtension;
use auth\User;
use openid\helpers\OpenIdErrorMessages;
use openid\requests\OpenIdAuthenticationRequest;
use openid\services\IServerConfigurationService;
use openid\services\IMementoOpenIdRequestService;
use Log;

/**
 * Class OpenIdAuthenticationExtension
 * @package openid\extensions
 */
class OpenIdAuthenticationExtension implements IAuthenticationExtension
{
    private $memento_service;
    private $server_configuration;

    /**
     * @param IMementoOpenIdRequestService $memento_service
     * @param IServerConfigurationService $server_configuration
     */
    public function __construct(IMementoOpenIdRequestService $memento_service, IServerConfigurationService $server_configuration){
        $this->server_configuration = $server_configuration;
        $this->memento_service      = $memento_service;
    }

    public function process(User $user)
    {
        //check if we have a current openid message
        $msg = $this->memento_service->getCurrentRequest();
        if (!is_null($msg) && $msg->isValid() && OpenIdAuthenticationRequest::IsOpenIdAuthenticationRequest($msg)) {
            //check if current user is has the same identity that the one claimed on openid message
            $auth_request = new OpenIdAuthenticationRequest($msg);
            if (!$auth_request->isIdentitySelectByOP()) {
                $claimed_id       = $auth_request->getClaimedId();
                $identity         = $auth_request->getIdentity();
                $current_identity = $this->server_configuration->getUserIdentityEndpointURL($user->getIdentifier());

                //if not return fail ( we cant log in with a different user that the one stated on the authentication message!
                if ($claimed_id !== $current_identity && $identity !== $current_identity) {
                    Log::warning(sprintf(OpenIdErrorMessages::AlreadyExistSessionMessage, $current_identity, $identity));
                    throw new AuthenticationException(sprintf(OpenIdErrorMessages::AlreadyExistSessionMessage, $current_identity, $identity));
                }
            }

        }
    }
}