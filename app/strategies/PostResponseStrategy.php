<?php
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

namespace strategies;


use utils\IHttpResponseStrategy;
use \Response;
/**
 * Class PostResponseStrategy
 * @package strategies
 */
final class PostResponseStrategy implements IHttpResponseStrategy
{

    public function handle($response)
    {
        $http_response = Response::make($response->getContent(), $response->getHttpCode());
        $http_response->header('Content-Type', $response->getContentType());
        $http_response->header('Cache-Control','no-cache, no-store, max-age=0, must-revalidate');
        $http_response->header('Pragma','no-cache');
        return $http_response;
    }
}