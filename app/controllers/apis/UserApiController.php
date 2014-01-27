<?php

use utils\services\ILogService;

class UserApiController extends JsonController{

    public function __construct(ILogService $log_service)
    {
        parent::__construct($log_service);
    }
} 