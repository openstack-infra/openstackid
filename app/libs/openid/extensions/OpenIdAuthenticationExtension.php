<?php

namespace openid\extensions;

use auth\exceptions\AuthenticationException;
use auth\IAuthenticationExtension;
use auth\User;
use openid\helpers\OpenIdErrorMessages;
use openid\OpenIdMessage;
use openid\requests\OpenIdAuthenticationRequest;
use openid\services\IMementoOpenIdSerializerService;
use openid\services\IServerConfigurationService;
use Log;

/**
 * Class OpenIdAuthenticationExtension
 * @package openid\extensions
 */
class OpenIdAuthenticationExtension implements IAuthenticationExtension
{
    /**
     * @var IMementoOpenIdSerializerService
     */
    private $memento_service;
    /**
     * @var IServerConfigurationService
     */
    private $server_configuration;

    /**
     * @param IMementoOpenIdSerializerService $memento_service
     * @param IServerConfigurationService $server_configuration
     */
    public function __construct(
        IMementoOpenIdSerializerService $memento_service,
        IServerConfigurationService $server_configuration
    )
    {
        $this->server_configuration = $server_configuration;
        $this->memento_service      = $memento_service;
    }

    public function process(User $user)
    {
        if(!$this->memento_service->exists()) return;

        //check if we have a current openid message
        $msg = OpenIdMessage::buildFromMemento($this->memento_service->load());

        if (!is_null($msg) && $msg->isValid() && OpenIdAuthenticationRequest::IsOpenIdAuthenticationRequest($msg))
        {
            //check if current user is has the same identity that the one claimed on openid message
            $auth_request = new OpenIdAuthenticationRequest($msg);
            if (!$auth_request->isIdentitySelectByOP())
            {
                $claimed_id       = $auth_request->getClaimedId();
                $identity         = $auth_request->getIdentity();
                $current_identity = $this->server_configuration->getUserIdentityEndpointURL($user->getIdentifier());

                //if not return fail ( we cant log in with a different user that the one stated on the authentication message!
                if ($claimed_id !== $current_identity && $identity !== $current_identity) {
                    Log::warning(sprintf(OpenIdErrorMessages::AlreadyExistSessionMessage, $current_identity, $identity));
                    throw new AuthenticationException
                    (
                        sprintf
                        (
                            OpenIdErrorMessages::AlreadyExistSessionMessage,
                            $current_identity,
                            $identity
                        )
                    );
                }
            }

        }
    }
}