<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 6:05 PM
 * To change this template use File | Settings | File Templates.
 */

use openid\IOpenIdProtocol;
use openid\OpenIdMessage;
use openid\strategies\OpenIdResponseStrategyFactoryMethod;
use openid\exceptions\InvalidOpenIdMessageException;
use openid\services\IMementoOpenIdRequestService;

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
        if (is_null($msg) || !$msg->IsValid())
            throw new InvalidOpenIdMessageException("there is not a valid OpenIdMessage set on request");
        //get response and manage it taking in consideration its type (direct or indirect)
        $response = $this->openid_protocol->HandleOpenIdMessage($msg);
        $reflector = new ReflectionClass($response);
        if($reflector->isSubclassOf("openid\\responses\\OpenIdResponse")){
            $strategy = OpenIdResponseStrategyFactoryMethod::buildStrategy($response);
            return $strategy->handle($response);
        }
        return $response;
    }
}