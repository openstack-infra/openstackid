<?php

use oauth2\IResourceServerContext;
use utils\services\ILogService;

/**
 * Class OAuth2ProtectedController
 * OAuth2 Protected Base API
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