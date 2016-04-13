<?php namespace App\Http\Middleware;
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
use Illuminate\Support\Facades\Response;
use Utils\Services\ServiceLocator;
use Utils\Services\UtilsServiceCatalog;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;

/**
 * Class CurrentUserCheckRouteParams
 * @package App\Http\Middleware
 */
class CurrentUserCheckRouteParams
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
        try{
            $route                  = Route::getCurrentRoute();
            $authentication_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::AuthenticationService);
            $used_id                = Input::get('user_id',null);

            if(is_null($used_id))
                $used_id            = Input::get('id',null);

            if(is_null($used_id))
                $used_id =  $route->getParameter('user_id');

            if(is_null($used_id))
                $used_id =  $route->getParameter('id');

            $user                   = $authentication_service->getCurrentUser();
            if (is_null($used_id) || intval($used_id) !== intval($user->getId()))
                throw new Exception(sprintf('user id %s does not match with current user id %s',$used_id,$user->getId()));

        } catch (Exception $ex) {
            Log::error($ex);
            return Response::json(array('error' => 'operation not allowed.'), 400);
        }
        return $next($request);
    }
}