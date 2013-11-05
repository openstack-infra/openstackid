<?php

class UserTest extends TestCase
{

    public function testMember()
    {
        $member = Member::findOrFail(1);
        $this->assertTrue($member->FirstName == 'Todd');
    }

    public function testOpenIdUserAssociation()
    {
        $username = 'sebastian@tipit.net';
        $password = 'Koguryo@1981';
        $member = Member::where('Email', '=', $username)->firstOrFail();
        $this->assertTrue($member->checkPassword($password));
    }
}