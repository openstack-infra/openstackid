<?php namespace App\Http\Controllers\Api;

/**
 * Copyright 2015 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use Utils\Services\ILogService;
use Illuminate\Support\Facades\Response;

/**
 * Class JsonController
 * @package App\Http\Controllers
 */
abstract class JsonController extends Controller  {

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

    protected function updated($data='ok')
    {
        $res =  Response::json($data, 204);
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

        return Response::json(array('error'=>'validation' , 'messages' => $messages), 412);
    }
} 