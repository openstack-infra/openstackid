<?php namespace App\Http\Middleware;
/**
 * Copyright 2015 OpenStack Foundation
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
use Log;

/**
 * Class ETagsMiddleware
 * @package App\Http\Middleware
 */
final class ETagsMiddleware
{


    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if ($response->getStatusCode() === 200 && $request->getMethod() === 'GET')
        {
            $etag = md5($response->getContent());
            $requestETag = str_replace('"', '', $request->getETags());
            $requestETag = str_replace('-gzip', '', $requestETag);

            if ($requestETag && $requestETag[0] == $etag)
            {
                Log::debug('ETAG 304');
                $response->setNotModified();
            }
            $response->setEtag($etag);
        }

        return $response;
    }
}