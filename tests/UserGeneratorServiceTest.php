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
use Auth\UserNameGeneratorService;
use Auth\Repositories\IMemberRepository;
use Tests\BrowserKitTestCase;
/**
 * Class UserGeneratorServiceTest
 */
final class UserGeneratorServiceTest extends BrowserKitTestCase {

    protected function prepareForTests()
    {
        parent::prepareForTests();
    }

    public function testBuildUsers()
    {

        $member_repository           = App::make(IMemberRepository::class);
        $user_name_service_generator = new UserNameGeneratorService();

        $member1 = $member_repository->getByEmail("mkiss@tipit.net");
        $member2 = $member_repository->getByEmail("fujg573@tipit.net");
        $member3 = $member_repository->getByEmail("mrbharathee@tipit.com");
        $member4 = $member_repository->getByEmail("yuanying@tipit.com");

        $user_name_1 = $user_name_service_generator->generate($member1);
        $this->assertTrue($user_name_1 === 'marton.kiss');

        $user_name_2 = $user_name_service_generator->generate($member2);
        $this->assertTrue($user_name_2 === 'fujg573');

        $user_name_3 = $user_name_service_generator->generate($member3);
        $this->assertTrue($user_name_3 === 'bharath.kumar.m.r');

        $user_name_4 = $user_name_service_generator->generate($member4);
        $this->assertTrue($user_name_4 === 'yuanying');

    }

}