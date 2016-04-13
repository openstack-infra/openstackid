<?php namespace OpenId\Extensions;
/**
 * Copyright 2016 OpenStack Foundation
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
use Auth\Exceptions\AuthenticationException;
use Auth\IAuthenticationExtension;
use Auth\User;
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\OpenIdMessage;
use OpenId\Requests\OpenIdAuthenticationRequest;
use OpenId\Services\IMementoOpenIdSerializerService;
use OpenId\Services\IServerConfigurationService;
use Log;
/**
 * Class OpenIdAuthenticationExtension
 * @package OpenId\Extensions
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

    /**
     * @param User $user
     * @return void
     * @throws AuthenticationException
     */
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