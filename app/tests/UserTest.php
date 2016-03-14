<?php

use auth\User;
use openid\services\OpenIdServiceCatalog;

class UserTest extends TestCase
{

    public function testMember()
    {
        $member = Member::findOrFail(1);
        $this->assertTrue($member->FirstName == 'Sebastian');
    }

    public function testLockUser()
    {
        $member = Member::findOrFail(1);
        $this->assertTrue($member->FirstName == 'Sebastian');

        $user = User::where('identifier','=','sebastian.marcet')->first();
        $service = App::make(OpenIdServiceCatalog::UserService);
        $service->lockUser($user->id);
    }
}