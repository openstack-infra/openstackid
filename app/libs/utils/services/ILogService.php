<?php

namespace utils\services;

use Exception;

/**
 * Interface ILogService
 * @package utils\services
 */
interface ILogService {
    public function error(Exception $exception);

    public function warning(Exception $exception);

    public function warning_msg($msg);

    public function error_msg($msg);

    public function info(Exception $exception);
}