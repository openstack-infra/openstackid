<?php namespace Http\Middleware;
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
use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Utils\Services\ICheckPointService;
use Utils\Services\ServiceLocator;
use Utils\Services\UtilsServiceCatalog;
/**
 * Class SingleAccessPoint
 * @package Http\Middleware
 */
final class SingleAccessPoint
{
    public function handle($request, Closure $next)
    {
        // Perform action
        if(Config::get('server.Banning_Enable', true))
        {
            try {
                //checkpoint security pattern entry point
                $checkpoint_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::CheckPointService);
                if ($checkpoint_service instanceof ICheckPointService && !$checkpoint_service->check()) {
                    return Response::view('404', array(), 404);
                }
            } catch (Exception $ex) {
                Log::error($ex);
                return Response::view('404', array(), 404);
            }
        }
        return $next($request);
    }
}