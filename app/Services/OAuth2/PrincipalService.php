<?php namespace Services\OAuth2;
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
use phpseclib\Crypt\Random;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use OAuth2\Models\IPrincipal;
use OAuth2\Models\Principal;
use OAuth2\Services\IPrincipalService;
/**
 * Class PrincipalService
 * @package Services\OAuth2
 */
final class PrincipalService implements IPrincipalService
{

    const UserIdParam    = 'openstackid.oauth2.principal.user_id';
    const AuthTimeParam  = 'openstackid.oauth2.principal.auth_time';
    const OPBrowserState = 'openstackid.oauth2.principal.opbs';

    /**
     * @return IPrincipal
     */
    public function get()
    {
        $principal = new Principal;

        $user_id   = Session::get(self::UserIdParam);
        $auth_time = Session::get(self::AuthTimeParam);
        $ops       = Session::get(self::OPBrowserState);

        $principal->setState
        (
            array
            (
                $user_id,
                $auth_time,
                $ops
            )
        );

        return $principal;
    }

    /**
     * @param IPrincipal $principal
     * @return void
     */
    public function save(IPrincipal $principal)
    {
        Log::debug("PrincipalService::save");

        $this->register
        (
            $principal->getUserId(),
            $principal->getAuthTime()
        );
    }

    /**
     * @param int $user_id
     * @param int $auth_time
     * @return mixed
     */
    public function register($user_id, $auth_time)
    {
        Log::debug(sprintf("PrincipalService::register user_id %s auth_time %s", $user_id, $auth_time));
        Session::put(self::UserIdParam, $user_id);
        Session::put(self::AuthTimeParam, $auth_time);
        $opbs = bin2hex(Random::string(16));
        Cookie::queue(IPrincipalService::OP_BROWSER_STATE_COOKIE_NAME, $opbs, $minutes = config("session.op_browser_state_lifetime"), $path = '/', $domain = null, $secure = false, $httpOnly = false);
        Log::debug(sprintf("PrincipalService::register opbs %s", $opbs));
        Session::put(self::OPBrowserState, $opbs);
        Session::save();
    }

    /**
     * @return $this
     */
    public function clear()
    {
        Log::debug("PrincipalService::clear");
        Session::remove(self::UserIdParam);
        Session::remove(self::AuthTimeParam);
        Session::remove(self::OPBrowserState);
        Session::save();
        Cookie::queue(IPrincipalService::OP_BROWSER_STATE_COOKIE_NAME, null, $minutes = -2628000, $path = '/', $domain = null, $secure = false, $httpOnly = false);
    }

}