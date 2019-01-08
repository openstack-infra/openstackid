<?php
use Auth\User;
use Models\Member;
use OpenId\Services\OpenIdServiceCatalog;
use Illuminate\Support\Facades\App;
use Auth\UserNameGeneratorService;
use Tests\BrowserKitTestCase;
/**
 * Class UserTest
 */
class UserTest extends BrowserKitTestCase
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

    public function testUserNameGeneration(){
        $generator = new UserNameGeneratorService();
        $member6 = Member::findOrFail(6);
        $member7 = Member::findOrFail(7);
        $member8 = Member::findOrFail(8);
        $member9 = Member::findOrFail(9);
        $id6 = $generator->generate($member6);
        $this->assertTrue( $id6 == 'bharath.kumar.m.r');
        $id7 = $generator->generate($member7);
        $this->assertTrue( $id7 == 'yuanying');
        $id8 = $generator->generate($member8);
        $this->assertTrue( $id8 == 'sebastian.german.marcet.gomez');
        $id9 = $generator->generate($member9);
        $this->assertTrue( $id9 == 'ian.y.choi');
    }
}