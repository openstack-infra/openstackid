<?php
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

namespace strategies;

use oauth2\OAuth2Protocol;
/**
 * Class DisplayResponseStrategyFactory
 * @package strategies
 */
final class DisplayResponseStrategyFactory
{
    /**
     * @param string $display
     * @return IDisplayResponseStrategy
     */
    static public function  build($display)
    {
       switch($display)
       {
            case OAuth2Protocol::OAuth2Protocol_Display_Native:
                 return new DisplayResponseJsonStrategy;
            break;
            default:
                 return new DisplayResponseUserAgentStrategy;
            break;
        }
        return null;
    }
}