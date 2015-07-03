<?php

use openid\exceptions\InvalidOpenIdMessageException;
use openid\helpers\OpenIdErrorMessages;
use openid\IOpenIdProtocol;
use openid\services\IMementoOpenIdSerializerService;
use openid\strategies\OpenIdResponseStrategyFactoryMethod;
use openid\OpenIdMessage;
use openid\responses\OpenIdResponse;
/**
 * Class OpenIdProviderController
 */
class OpenIdProviderController extends BaseController
{
    /**
     * @var IOpenIdProtocol
     */
    private $openid_protocol;
    /**
     * @var IMementoOpenIdSerializerService
     */
    private $memento_service;

    /**
     * @param IOpenIdProtocol $openid_protocol
     * @param IMementoOpenIdSerializerService $memento_service
     */
    public function __construct(IOpenIdProtocol $openid_protocol, IMementoOpenIdSerializerService $memento_service)
    {
        $this->openid_protocol = $openid_protocol;
        $this->memento_service = $memento_service;
    }

    /**
     * @return OpenIdResponse
     * @throws Exception
     * @throws InvalidOpenIdMessageException
     */
    public function endpoint()
    {
        $msg = new OpenIdMessage( Input::all() );

        if($this->memento_service->exists()){
            $msg = OpenIdMessage::buildFromMemento( $this->memento_service->load());
        }

        if (!$msg->isValid())
            throw new InvalidOpenIdMessageException(OpenIdErrorMessages::InvalidOpenIdMessage);

        //get response and manage it taking in consideration its type (direct or indirect)
        $response = $this->openid_protocol->handleOpenIdMessage($msg);

        if ($response instanceof OpenIdResponse) {
            $strategy = OpenIdResponseStrategyFactoryMethod::buildStrategy($response);
            return $strategy->handle($response);
        }
        return $response;
    }
}