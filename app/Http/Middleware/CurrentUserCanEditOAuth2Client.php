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
use OAuth2\Repositories\IClientRepository;
use Utils\Services\IAuthService;
use Utils\Services\ServiceLocator;
use Utils\Services\UtilsServiceCatalog;
use OAuth2\Services\OAuth2ServiceCatalog;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;

/**
 * Class CurrentUserCanEditOAuth2Client
 * @package App\Http\Middleware
 */
final class CurrentUserCanEditOAuth2Client
{

    /**
     * @var IClientRepository
     */
    private $client_repository;

    /**
     * @var IAuthService
     */
    private $auth_service;

    public function __construct(IClientRepository $client_repository, IAuthService $auth_service)
    {
        $this->client_repository = $client_repository;
        $this->auth_service      = $auth_service;
    }

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
            $client_id              = $route->parameter('id');

            if(is_null($client_id))
                $client_id          = $route->parameter('client_id');

            if(is_null($client_id))
                $client_id          = Input::get('client_id',null);;

            $client                 = $this->client_repository->getClientByIdentifier($client_id);
            $user                   = $this->auth_service->getCurrentUser();

            if (is_null($client) || !$client->candEdit($user))
                throw new Exception('invalid client id for current user');

        } catch (Exception $ex) {
            Log::error($ex);
            return Response::json(array('error' => 'operation not allowed.'), 400);
        }
        return $next($request);
    }
}