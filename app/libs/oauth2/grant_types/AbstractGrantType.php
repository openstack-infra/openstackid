<?php

namespace oauth2\grant_types;

use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\UnAuthorizedClientException;
use oauth2\models\IClient;
use oauth2\requests\OAuth2Request;
use oauth2\services\IClientService;

use oauth2\services\ITokenService;

use utils\services\ILogService;

abstract class AbstractGrantType implements IGrantType
{

    protected $client_service;
    protected $token_service;

    //authorization info
    protected $current_client_id;
    protected $current_client_secret;
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
     * @throws \oauth2\exceptions\UnAuthorizedClientException
     * @throws \oauth2\exceptions\InvalidClientException
     */
    public function completeFlow(OAuth2Request $request)
    {
        //get client credentials from request..
        list($this->current_client_id, $this->current_client_secret) = $this->client_service->getCurrentClientAuthInfo();

        //check if we have at least a client id
        if (empty($this->current_client_id))
            throw new InvalidClientException;

        //retrieve client from storage..
        $this->current_client = $this->client_service->getClientById($this->current_client_id);

        if (is_null($this->current_client))
            throw new InvalidClientException;

        if (!$this->current_client->isActive() || $this->current_client->isLocked()) {
            throw new UnAuthorizedClientException();
        }

        //verify client credentials (only for confidential clients )
        if ($this->current_client->getClientType() == IClient::ClientType_Confidential && $this->current_client->getClientSecret() !== $this->current_client_secret)
            throw new UnAuthorizedClientException;

    }
} 