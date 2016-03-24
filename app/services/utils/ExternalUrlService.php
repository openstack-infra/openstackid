<?php
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

namespace services\utils;

use Jenssegers\Agent\Agent;
use services\facades\ServerConfigurationService as ConfigFacade;


/**
 * Class ExternalUrlService
 * @package services\utils
 */
final class ExternalUrlService
{
    public function getCreateAccountUrl(){
        $agent = new Agent();
        $path  = $agent->isMobile() ? 'join/register/mobile/community' : 'join/register';
        return sprintf("%s%s",ConfigFacade::getConfigValue("Assets.Url"),$path);
    }

    public function getVerifyAccountUrl(){
        $path  = 'members/verification/resend';
        return sprintf("%s%s",ConfigFacade::getConfigValue("Assets.Url"),$path);
    }

    public function getForgotPasswordUrl(){
        $path  = 'Security/lostpassword';
        return sprintf("%s%s",ConfigFacade::getConfigValue("Assets.Url"),$path);
    }
}