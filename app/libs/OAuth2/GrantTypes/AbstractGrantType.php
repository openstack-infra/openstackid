<?php namespace OAuth2\GrantTypes;

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

use OAuth2\Exceptions\InvalidClientCredentials;
use OAuth2\Exceptions\InvalidClientException;
use OAuth2\Exceptions\LockedClientException;
use OAuth2\Exceptions\MissingClientIdParam;
use OAuth2\Models\ClientAuthenticationContext;
use OAuth2\Models\IClient;
use OAuth2\Repositories\IClientRepository;
use OAuth2\Services\ITokenService;
use OAuth2\Requests\OAuth2Request;
use OAuth2\Responses\OAuth2Response;
use OAuth2\Services\IClientService;
use OAuth2\Strategies\ClientAuthContextValidatorFactory;
use Utils\Services\ILogService;

/**
 * Class AbstractGrantType
 * @package OAuth2\GrantTypes
 */
abstract class AbstractGrantType implements IGrantType
{

    /**
     * @var ClientAuthenticationContext
     */
    protected $client_auth_context;
    /**
     * @var IClient
     */
    protected $current_client;

    /**
     * @var IClientService
     */
    protected $client_service;
    /**
     * @var ITokenService
     */
    protected $token_service;

    /**
     * @var ILogService
     */
    protected $log_service;

    /**
     * @var IClientService
     */
    protected $client_repository;

    /**
     * AbstractGrantType constructor.
     * @param IClientService $client_service
     * @param IClientRepository $client_repository
     * @param ITokenService $token_service
     * @param ILogService $log_service
     */
    public function __construct
    (
        IClientService     $client_service,
        IClientRepository  $client_repository,
        ITokenService      $token_service,
        ILogService        $log_service
    )
    {
        $this->client_service    = $client_service;
        $this->client_repository = $client_repository;
        $this->token_service     = $token_service;
        $this->log_service       = $log_service;
    }

    /**
     * @param OAuth2Request $request
     * @return OAuth2Response
     * @throws MissingClientIdParam
     * @throws InvalidClientCredentials
     * @throws InvalidClientException
     * @throws LockedClientException
     */
    public function completeFlow(OAuth2Request $request)
    {
        //get client credentials from request..
        $this->client_auth_context = $this->client_service->getCurrentClientAuthInfo();


        //retrieve client from storage..
        $this->current_client = $this->client_repository->getClientById($this->client_auth_context->getId());

        if (is_null($this->current_client))
            throw new InvalidClientException
            (
                sprintf
                (
                    "client id %s does not exists!",
                    $this->client_auth_context->getId()
                )
            );

        if (!$this->current_client->isActive() || $this->current_client->isLocked()) {
            throw new LockedClientException
            (
                sprintf
                (
                    'client id %s is locked.',
                    $this->client_auth_context->getId()
                )
            );
        }

        $this->client_auth_context->setClient($this->current_client);

        if(!ClientAuthContextValidatorFactory::build($this->client_auth_context)->validate($this->client_auth_context))
            throw new InvalidClientCredentials
            (
                sprintf
                (
                    'invalid credentials for client id %s.',
                    $this->client_auth_context->getId()
                )
            );
    }

} 