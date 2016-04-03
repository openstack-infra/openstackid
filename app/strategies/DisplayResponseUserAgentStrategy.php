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

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Illuminate\Support\Facades\Response;
use Redirect;

/**
 * Class DisplayResponseUserAgentStrategy
 * @package strategies
 */
class DisplayResponseUserAgentStrategy implements IDisplayResponseStrategy
{

    /**
     * @param array $data
     * @return SymfonyResponse
     */
    public function getConsentResponse(array $data = array())
    {
        return Response::view("oauth2.consent", $data, 200);
    }

    /**
     * @param array $data
     * @return SymfonyResponse
     */
    public function getLoginResponse(array $data = array())
    {
        return Response::view("login", $data, 200);
    }

    /**
     * @param array $data
     * @return SymfonyResponse
     */
    public function getLoginErrorResponse(array $data = array())
    {
        $response =  Redirect::action('UserController@getLogin')
            ->with('max_login_attempts_2_show_captcha', $data['max_login_attempts_2_show_captcha'])
            ->with('login_attempts', $data['login_attempts']);

        if(isset($data['username']))
            $response= $response->with('username', $data['username']);
        if(isset($data['error_message']))
            $response = $response->with('flash_notice', $data['error_message']);
        if(isset($data['validator']))
            $response = $response->withErrors($data['validator']);

        return $response;
    }
}