<?php namespace App\Http;
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
use Illuminate\Foundation\Http\Kernel as HttpKernel;
/**
 * Class Kernel
 * @package App\Http
 */
class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\SingleAccessPoint::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ],

        'api' => [
            'ssl',
            'cors',
            'oauth2',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'                                     => \App\Http\Middleware\Authenticate::class,
        'auth.basic'                               => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'ssl'                                      => \App\Http\Middleware\SSLMiddleware::class,
        'can'                                      => \Illuminate\Foundation\Http\Middleware\Authorize::class,
        'guest'                                    => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle'                                 => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'csrf'                                     => \App\Http\Middleware\VerifyCsrfToken::class,
        'oauth2.endpoint'                          => \App\Http\Middleware\OAuth2BearerAccessTokenRequestValidator::class,
        'cors'                                     => \App\Http\Middleware\CORSMiddleware::class,
        'oauth2.currentuser.serveradmin'           => \App\Http\Middleware\CurrentUserIsOAuth2ServerAdmin::class,
        'oauth2.currentuser.serveradmin.json'      => \App\Http\Middleware\CurrentUserIsOAuth2ServerAdminJson::class,
        'openstackid.currentuser.serveradmin'      => \App\Http\Middleware\CurrentUserIsOpenIdServerAdmin::class,
        'openstackid.currentuser.serveradmin.json' => \App\Http\Middleware\CurrentUserIsOpenIdServerAdminJson::class,
        'oauth2.currentuser.allow.client.edition'  => \App\Http\Middleware\CurrentUserCanEditOAuth2Client::class,
        'oauth2.currentuser.owns.client'           => \App\Http\Middleware\CurrentUserOwnsOAuth2Client::class,
        'currentuser.checkroute'                   => \App\Http\Middleware\CurrentUserCheckRouteParams::class,
    ];
}
