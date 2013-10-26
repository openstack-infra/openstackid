<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/26/13
 * Time: 5:18 PM
 */

namespace openid\services;
use \Exception;

interface ILogService {
    public function error(Exception $exception);
    public function warning(Exception $exception);
    public function warning_msg($msg);
    public function info(Exception $exception);
} 