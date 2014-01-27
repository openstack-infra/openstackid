<?php

use oauth2\IResourceServerContext;
use utils\services\ILogService;

/**
 * Class OAuth2ProtectedController
 * OAuth2 Protected Base API
 */
class OAuth2ProtectedController extends JsonController {

    protected $resource_server_context;

    public function __construct(IResourceServerContext $resource_server_context, ILogService $log_service)
    {
        parent::__construct($log_service);
        $this->resource_server_context = $resource_server_context;
    }
}