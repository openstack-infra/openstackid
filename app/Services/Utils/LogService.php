<?php namespace Services\Utils;
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

use Exception;
use Log;
use Utils\Services\ILogService;

/**
 * Class LogService
 * @package Services\Utils
 */
final class LogService implements ILogService
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

    public function debug_msg($msg)
    {
        Log::debug($msg);
    }
}