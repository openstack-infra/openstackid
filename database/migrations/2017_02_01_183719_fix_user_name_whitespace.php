<?php

use Illuminate\Database\Migrations\Migration;
use Auth\User;
use Auth\UserNameGeneratorService;
use Illuminate\Support\Facades\DB;
use Utils\Exceptions\EntityNotFoundException;

/**
 * Class FixUserNameWhitespace
 */
class FixUserNameWhitespace extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function(){

            $users     = User::where('identifier','like','% %')->get();
            $generator = new UserNameGeneratorService();

            foreach($users as $user_2_fix){
                try {

                    $member       = $user_2_fix->getMember();
                    $fragment_nbr = 1;
                    $identifier   = $original_identifier = $generator->generate($member);

                    do {
                        $old_user = User::where('identifier', '=', $identifier)->where('id', '<>', $user_2_fix->id)->first();
                        if (!is_null($old_user) && !$old_user->hasMember()){
                            sprintf("deleting user id %s", $old_user->id).PHP_EOL;
                            $old_user->delete();
                            $old_user = null;
                        }
                        if (!is_null($old_user)) {
                            echo sprintf("identifier %s collision with user id %s - member_id %s", $identifier, $old_user->id, $old_user->external_identifier).PHP_EOL;
                            $identifier = $original_identifier . \Auth\IUserNameGeneratorService::USER_NAME_CHAR_CONNECTOR . $fragment_nbr;
                            $fragment_nbr++;
                            continue;
                        }
                        $user_2_fix->identifier = $identifier;
                        break;
                    } while (1);

                    $user_2_fix->save();
                }
                catch (EntityNotFoundException $ex){
                    echo sprintf("member not found for user id %s - identifier %s ... deleting it", $user_2_fix->id, $user_2_fix->identifier).PHP_EOL;
                    $user_2_fix->delete();
                }
            }
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
