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
use Auth\Repositories\IMemberRepository;
use OpenId\Services\IUserService;
use Tests\BrowserKitTestCase;
/**
 * Class UserServiceTest
 */
final class UserServiceTest extends BrowserKitTestCase
{

    protected function prepareForTests()
    {
        parent::prepareForTests();
    }

    public function testBuildUsers()
    {
        $member_repository = App::make(IMemberRepository::class);
        $user_service      = App::make(IUserService::class);

        $member1 = $member_repository->getByEmail("sebastian@tipit.net");
        $member2 = $member_repository->getByEmail("sebastian+1@tipit.net");
        $member3 = $member_repository->getByEmail("sebastian+2@tipit.net");

        $user2 = $user_service->buildUser($member2);
        $user3 = $user_service->buildUser($member3);

        $this->assertTrue($user2->identifier === 'sebastian.marcet.1');
        $this->assertTrue($user3->identifier === 'sebastian.marcet.2');
    }
}