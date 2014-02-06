<?php

namespace services;

use Exception;
use Log;
use utils\services\ILogService;

class LogService implements ILogService
{

    public function error(Exception $exception)
    {
        Log::error($exception);
    }

    public function warning(Exception $exception)
    {
        Log::warning($exception);
    }

    public function info(Exception $exception)
    {
        Log::info($exception);
    }

    public function warning_msg($msg)
    {
        Log::warning($msg);
    }

    public function error_msg($msg)
    {
        Log::error($msg);
    }
}