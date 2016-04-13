<?php namespace OAuth2\ResourceServer;
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

use OAuth2\IResourceServerContext;
use Utils\Services\ILogService;

/**
 * Class OAuth2ProtectedService
 * Base Class for OAUTH2 protected endpoints
 * @package OAuth2\ResourceServer
 */
abstract class OAuth2ProtectedService
{

    /**
     * @var IResourceServerContext
     */
    protected $resource_server_context;
    /**
     * @var ILogService
     */
    protected $log_service;

    /**
     * @param IResourceServerContext $resource_server_context
     * @param ILogService $log_service
     */
    public function __construct(IResourceServerContext $resource_server_context, ILogService $log_service)
    {
        $this->log_service             = $log_service;
        $this->resource_server_context = $resource_server_context;
    }
} 