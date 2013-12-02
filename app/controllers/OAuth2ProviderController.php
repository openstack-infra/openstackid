<?php
use oauth2\IOAuth2Protocol;
use oauth2\services\IMementoOAuth2RequestService;

/**
 * Class OAuth2ProviderController
 */
class OAuth2ProviderController extends BaseController {

    private $oauth2_protocol;
    private $memento_service;

    /**
     * @param IOAuth2Protocol $oauth2_protocol
     * @param IMementoOAuth2RequestService $memento_service
     */
    public function __construct(IOAuth2Protocol $oauth2_protocol, IMementoOAuth2RequestService $memento_service){
        $this->oauth2_protocol = $oauth2_protocol;
        $this->memento_service = $memento_service;
    }

    public function authorize(){
        $request = $this->memento_service->getCurrentRequest();
        if (is_null($request) || !$request->isValid())
            throw new \Exception();
        $response = $this->$oauth2_protocol->authorize($request);
    }
} 