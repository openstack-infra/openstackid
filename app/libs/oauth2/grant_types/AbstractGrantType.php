<?php

namespace oauth2\grant_types;

use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\MissingClientIdParam;
use oauth2\exceptions\LockedClientException;
use oauth2\exceptions\InvalidClientCredentials;

use oauth2\models\ClientAuthenticationContext;
use oauth2\models\IClient;
use oauth2\requests\OAuth2Request;
use oauth2\services\IClientService;

use oauth2\services\ITokenService;

use oauth2\strategies\ClientAuthContextValidatorFactory;
use utils\services\ILogService;


/**
 * Class AbstractGrantType
 * @package oauth2\grant_types
 */
abstract class AbstractGrantType implements IGrantType
{

    protected $client_service;
    protected $token_service;

    //authorization info
    /**
     * @var ClientAuthenticationContext
     */
    protected $client_auth_context;
    protected $current_client;
    protected $log_service;

    public function __construct(IClientService $client_service, ITokenService $token_service, ILogService $log_service)
    {
        $this->client_service = $client_service;
        $this->token_service = $token_service;
        $this->log_service = $log_service;
    }

    /**
     * @param OAuth2Request $request
     * @return mixed|void
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
        $this->current_client = $this->client_service->getClientById($this->client_auth_context->getId());

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