<?php namespace Strategies;
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
use Utils\IHttpResponseStrategy;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
/**
 * Class IndirectResponseQueryStringStrategy
 * Redirect and http response using a 302 adding params on query string
 * @package Strategies
 */
class IndirectResponseQueryStringStrategy implements IHttpResponseStrategy
{

    /**
     * @param $response
     * @return mixed
     */
    public function handle($response)
    {
        $query_string = $response->getContent();
        $return_to    = $response->getReturnTo();

        if (is_null($return_to) || empty($return_to)) {
            return Response::view('404', array(), 404);
        }
        $return_to = (strpos($return_to, "?") == false) ? $return_to . "?" . $query_string : $return_to . "&" . $query_string;

        return Redirect::to($return_to)
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma','no-cache');
    }
}