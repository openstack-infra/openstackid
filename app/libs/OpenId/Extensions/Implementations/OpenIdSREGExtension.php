<?php namespace OpenId\Extensions\Implementations;
/**
 * Copyright 2016 OpenStack Foundation
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

use OpenId\Requests\OpenIdRequest;
use Utils\Services\IAuthService;
use Utils\Services\ILogService;

/**
 * Class OpenIdSREGExtension
 * Implements @see http://openid.net/specs/openid-simple-registration-extension-1_1-01.html
 * @package OpenId\Extensions\Implementations
 */
class OpenIdSREGExtension extends OpenIdSREGExtension_1_0
{

    const NamespaceUrl  = 'http://openid.net/extensions/sreg/1.1';

	/**
	 * @param              $name
	 * @param              $namespace
	 * @param              $view_name
	 * @param              $description
	 * @param IAuthService $auth_service
	 * @param ILogService  $log_service
	 */
	public function __construct($name, $namespace, $view_name , $description,
                                IAuthService $auth_service,
                                ILogService $log_service)
    {
        parent::__construct($name, $namespace, $view_name, $description, $auth_service, $log_service);
    }

    /**
     * @param OpenIdRequest $request
     * @return OpenIdSREGRequest_1_0
     */
    protected function buildRequest(OpenIdRequest $request){
        return new OpenIdSREGRequest($request->getMessage());
    }
}