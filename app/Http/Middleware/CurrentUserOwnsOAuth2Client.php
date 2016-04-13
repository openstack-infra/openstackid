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
use Illuminate\Support\Facades\Response;
use Utils\Services\ServiceLocator;
use Utils\Services\UtilsServiceCatalog;
use OAuth2\Services\OAuth2ServiceCatalog;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;
use Illuminate\Routing\Route;
/**
 * Class CurrentUserOwnsOAuth2Client
 * @package Http\Middleware
 */
class CurrentUserOwnsOAuth2Client
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
            $route                  = Route::current();
            $authentication_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::AuthenticationService);
            $client_service         = ServiceLocator::getInstance()->getService(OAuth2ServiceCatalog::ClientService);
            $client_id              = $route->getParameter('id');

            if(is_null($client_id))
                $client_id          = $route->getParameter('client_id');

            if(is_null($client_id))
                $client_id          = Input::get('client_id',null);;

            $client                 = $client_service->getClientByIdentifier($client_id);
            $user                   = $authentication_service->getCurrentUser();
            if (is_null($client) || !$client->isOwner($user))
                throw new Exception('invalid client id for current user');

        } catch (Exception $ex) {
            Log::error($ex);
            return Response::json(array('error' => 'operation not allowed.'), 400);
        }
        return $next($request);
    }
}