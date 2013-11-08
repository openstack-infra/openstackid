<?php

use openid\exceptions\InvalidOpenIdMessageException;
use openid\helpers\OpenIdErrorMessages;
use openid\IOpenIdProtocol;
use openid\services\IMementoOpenIdRequestService;
use openid\strategies\OpenIdResponseStrategyFactoryMethod;

class OpenIdProviderController extends BaseController
{
    private $openid_protocol;
    private $memento_service;

    public function __construct(IOpenIdProtocol $openid_protocol, IMementoOpenIdRequestService $memento_service)
    {
        $this->openid_protocol = $openid_protocol;
        $this->memento_service = $memento_service;
    }

    public function op_endpoint()
    {
        $msg = $this->memento_service->getCurrentRequest();
        if (is_null($msg) || !$msg->isValid())
            throw new InvalidOpenIdMessageException(OpenIdErrorMessages::InvalidOpenIdMessage);
        //get response and manage it taking in consideration its type (direct or indirect)
        $response = $this->openid_protocol->handleOpenIdMessage($msg);
        $reflector = new ReflectionClass($response);
        if ($reflector->isSubclassOf('openid\\responses\\OpenIdResponse')) {
            $strategy = OpenIdResponseStrategyFactoryMethod::buildStrategy($response);
            return $strategy->handle($response);
        }
        return $response;
    }
}