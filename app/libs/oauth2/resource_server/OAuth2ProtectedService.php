<?php

namespace oauth2\resource_server;

use oauth2\IResourceServerContext;
use utils\services\ILogService;

/**
 * Class OAuth2ProtectedService
 * Base Class for OAUTH2 protected endpoints
 * @package oauth2\resource_server
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