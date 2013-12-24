<?php

namespace oauth2\grant_types;

use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use oauth2\requests\OAuth2Request;

use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\UnAuthorizedClientException;

abstract class AbstractGrantType implements IGrantType {

    protected $client_service;
    protected $token_service;

    protected $current_client_id;
    protected $current_client_secret;
    protected $current_client;

    public function __construct(IClientService $client_service, ITokenService $token_service)
    {
        $this->client_service = $client_service;
        $this->token_service  = $token_service;
    }

    /**
     * @param OAuth2Request $request
     * @throws \oauth2\exceptions\UnAuthorizedClientException
     * @throws \oauth2\exceptions\InvalidClientException
     */
    public function completeFlow(OAuth2Request $request)
    {
        //get client credentials from request..
        list($this->current_client_id, $this->current_client_secret) = $this->client_service->getCurrentClientAuthInfo();

        if (empty($this->current_client_id) || empty($this->current_client_secret))
            throw new InvalidClientException;

        //retrieve client from storage..
        $this->current_client = $this->client_service->getClientById($this->current_client_id);

        if (is_null($this->current_client))
            throw new InvalidClientException;

        if(!$this->current_client->isActive() || $this->current_client->isLocked()){
            throw new UnAuthorizedClientException();
        }

        //verify client credentials
        if ($this->current_client->getClientSecret() !== $this->current_client_secret)
            throw new UnAuthorizedClientException;

    }
} 