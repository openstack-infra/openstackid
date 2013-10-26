<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/26/13
 * Time: 5:22 PM
 */

namespace services;
use Exception;
use openid\services\ILogService;

class LogService implements  ILogService {

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
}