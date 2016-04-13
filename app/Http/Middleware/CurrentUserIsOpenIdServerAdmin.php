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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
/**
 * Class CurrentUserIsOpenIdServerAdmin
 * @package Http\Middleware
 */
final class CurrentUserIsOpenIdServerAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest())
        {
            return Response::view('error.404', array(), 404);
        }
        if(!Auth::user()->isOpenstackIdAdmin())
        {
            return Response::view('error.404', array(), 404);
        }
        return $next($request);
    }
}