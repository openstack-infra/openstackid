<?php

use utils\services\ILogService;

/**
 * Class JsonController
 */
class JsonController extends BaseController  {

    protected $log_service;

    public function __construct(ILogService $log_service)
    {
        $this->log_service = $log_service;
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