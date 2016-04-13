<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Auth\User;

class UpdateIdentifierOnOpenidUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        //get all users
		$users = User::all();
        // and update new field external_identifier with member id
        foreach($users as $user){
            $email  = $user->external_id;
            $member = Member::where('Email', '=', $email)->first();
            if(!is_null($member)){
                $user->external_identifier = $member->ID;
                $user->save();
            }
        }
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
