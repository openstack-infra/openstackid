<?php

use oauth2\IResourceServerContext;
use utils\services\ILogService;

class OAuth2ProtectedController extends BaseController {

    protected $log_service;
    protected $resource_server_context;

    public function __construct(IResourceServerContext $resource_server_context, ILogService $log_service)
    {
        $this->resource_server_context = $resource_server_context;
        $this->log_service             = $log_service;
    }

    protected function error500(Exception $ex){
        $this->log_service->error($ex);
        return Response::json(array('error' => 'server error'), 500);
    }

    protected function ok($data){
        return Response::json($data, 200);
    }

    protected function error400($data){
        return Response::json($data, 400);
    }

    protected function error404($data){
        return Response::json($data, 404);
    }

} 