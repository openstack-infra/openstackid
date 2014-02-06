<?php

use oauth2\IResourceServerContext;
use utils\services\ILogService;
use oauth2\resource_server\IUserService;

/**
 * Class OAuth2UserApiController
 * OAUTH2 Protected User REST API
 */
class OAuth2UserApiController extends OAuth2ProtectedController {

    public function __construct (IUserService $user_service, IResourceServerContext $resource_server_context, ILogService $log_service){
        parent::__construct($resource_server_context,$log_service);
        $this->user_service = $user_service;
    }

    /**
     * Gets User Basic Info
     * @return mixed
     */
    public function me(){
        try{
            $data = $this->user_service->getCurrentUserInfo();
            return $this->ok($data);
        }
        catch(Exception $ex){
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }
}

