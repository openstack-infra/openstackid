<?php namespace Utils\Http;
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
/**
 * Class HttpContentType
 * @package Utils\Http
 */
abstract class HttpContentType
{
    /**
     *  https://tools.ietf.org/html/rfc4627
     *  The application/json Media Type for JavaScript Object Notation (JSON)
     */
    const Json = 'application/json;charset=UTF-8';
    /**
     *   application/jwt MIME Media Type
     */
    const JWT  = 'application/jwt';
    /**
     *  https://tools.ietf.org/html/rfc2646
     */
    const Text = 'text/plain';
    /**
     *
     */
    const Html = 'text/html;charset=UTF-8';
    /**
     *  Form-urlencoded Data
     */
    const Form = 'application/x-www-form-urlencoded';
}