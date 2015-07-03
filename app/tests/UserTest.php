<?php

class UserTest extends TestCase
{

    public function testMember()
    {
        $member = Member::findOrFail(1);
        $this->assertTrue($member->FirstName == 'Sebastian');
    }
}