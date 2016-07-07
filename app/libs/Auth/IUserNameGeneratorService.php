<?php namespace Auth;
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
use Models\Member;
/**
 * Interface IUserNameGeneratorService
 * @package Auth
 */
interface IUserNameGeneratorService
{
    /**
     * @param Member $member
     * @return string
     */
    public function generate(Member $member);

    const USER_NAME_INVALID_CHAR_REPLACEMENT = '.';

    const USER_NAME_CHAR_CONNECTOR           = '.';
}