<?php

use oauth2\services\IApiScopeService;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2AuthenticationRequestService;
use oauth2\services\ITokenService;
use oauth2\services\IResourceServerService;

class AdminController extends BaseController {


    private $client_service;
    private $scope_service;
    private $token_service;
    private $resource_server_service;

    public function __construct( IClientService $client_service,
                                 IApiScopeService $scope_service,
                                 ITokenService $token_service,
                                 IResourceServerService $resource_server_service){
        $this->client_service = $client_service;
        $this->scope_service = $scope_service;
        $this->token_service = $token_service;
        $this->resource_server_service = $resource_server_service;
    }

    public function listResourceServers() {
        $resource_servers = $this->resource_server_service->getAll(1000,1);
        return View::make("oauth2.profile.admin.resource-servers",array('resource_servers'=>$resource_servers));
    }

    public function editResourceServer($id){
        $resource_server = $this->resource_server_service->get($id);
        if(is_null($resource_server))
            return View::make('404');
        return View::make("oauth2.profile.admin.edit-resource-server",array('resource_server'=>$resource_server));
    }

    public function editApi($id){
        return View::make("oauth2.profile.admin.edit-api",array());
    }
} 