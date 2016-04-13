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
use Illuminate\Contracts\Support\MessageProvider;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Illuminate\Support\Facades\Response;
use Utils\Services\IAuthService;
use Illuminate\Support\Facades\URL;
/**
 * Class DisplayResponseJsonStrategy
 * @package Strategies
 */
class DisplayResponseJsonStrategy implements IDisplayResponseStrategy
{

    /**
     * @param array $data
     * @return SymfonyResponse
     */
    public function getConsentResponse(array $data = array())
    {
        // fix scopes
        $requested_scopes                     = $data['requested_scopes'];
        $data['requested_scopes']             = array();
        foreach($requested_scopes as $scope)
        {
            array_push($data['requested_scopes'], $scope->toArray());
        }

        $data['required_params']              = array('_token', 'trust');
        $data['required_params_valid_values'] = array
        (
            'trust' => array
            (
                IAuthService::AuthorizationResponse_AllowOnce,
                IAuthService::AuthorizationResponse_DenyOnce,
            ),
            '_token' => csrf_token()
        );
        $data['optional_params'] = array();
        $data['url']             = URL::action('UserController@postConsent');
        $data['method']          = 'POST';
        return Response::json($data, 412);
    }

    /**
     * @param array $data
     * @return SymfonyResponse
     */
    public function getLoginResponse(array $data = array())
    {
        $data['required_params'] = array('username','password', '_token');
        $data['optional_params'] = array('remember');
        $data['url']             = URL::action('UserController@postLogin');
        $data['method']          = 'POST';

        if(!isset($data['required_params_valid_values']))
        {
            $data['required_params_valid_values'] = array();
        }

        $data['required_params_valid_values']['_token'] = csrf_token();
        return Response::json($data, 412);
    }

    /**
     * @param array $data
     * @return SymfonyResponse
     */
    public function getLoginErrorResponse(array $data = array())
    {
        if(isset($data['validator']) && $data['validator'] instanceof MessageProvider )
        {
            $validator = $data['validator'];
            unset($data['validator']);
            $data['error_message'] = array();
            $errors = $validator->getMessageBag()->getMessages();
            foreach($errors as $e)
            {
                array_push($data['error_message'],$e[0]);
            }
        }
        return Response::json($data, 412);
    }
}