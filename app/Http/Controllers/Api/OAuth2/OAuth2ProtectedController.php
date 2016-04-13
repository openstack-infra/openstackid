<?php namespace App\Http\Controllers\Api\OAuth2;
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

use OAuth2\IResourceServerContext;
use Utils\Services\ILogService;
use App\Http\Controllers\Api\JsonController;

/**
 * Class OAuth2ProtectedController
 * @package App\Http\Controllers\Api\OAuth2
 */
abstract class OAuth2ProtectedController extends JsonController
{

    /**
     * @var IResourceServerContext
     */
    protected $resource_server_context;

    /**
     * @var
     */
    protected $repository;

    /**
     * OAuth2ProtectedController constructor.
     * @param IResourceServerContext $resource_server_context
     * @param ILogService $log_service
     */
    public function __construct
    (
        IResourceServerContext $resource_server_context,
        ILogService $log_service
    )
    {
        parent::__construct($log_service);
        $this->resource_server_context = $resource_server_context;
    }
}