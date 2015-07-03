<?php

use utils\services\ILogService;

/**
 * Class JsonController
 */
abstract class JsonController extends BaseController  {

    protected $log_service;

    public function __construct(ILogService $log_service)
    {
        $this->log_service = $log_service;
    }

    protected function error500(Exception $ex){
        $this->log_service->error($ex);
        return Response::json(array( 'error' => 'server error'), 500);
    }

    protected function created($data='ok'){
        $res = Response::json($data, 201);
        //jsonp
        if(Input::has('callback'))
            $res->setCallback(Input::get('callback'));
        return $res;
    }

    protected function deleted($data='ok'){
        $res =  Response::json($data, 204);
        //jsonp
        if(Input::has('callback'))
            $res->setCallback(Input::get('callback'));
        return $res;
    }

    protected function ok($data = 'ok'){
        $res = Response::json($data, 200);
        //jsonp
        if(Input::has('callback'))
            $res->setCallback(Input::get('callback'));
        return $res;
    }

    protected function error400($data){
        return Response::json($data, 400);
    }

    protected function error404($data = array('message' => 'Entity Not Found')){
        return Response::json($data, 404);
    }

    /**
     *  {
        "message": "Validation Failed",
        "errors": [
        {
        "resource": "Issue",
        "field": "title",
        "code": "missing_field"
        }
        ]
        }
     * @param $messages
     * @return mixed
     */
    protected function error412($messages){

        return Response::json(array('message' => 'Validation Failed', 'errors' => $messages), 412);
    }
} 