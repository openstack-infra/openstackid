<?php

use Illuminate\Database\Migrations\Migration;
use Models\OAuth2\Api;
use Models\OAuth2\ApiEndpoint;
use Models\OAuth2\ApiScope;

class AddUserInfoOIDCEndpoint extends Migration
{

    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {

        $users = Api::where('name', '=', 'users')->first();

        if(is_null($users)) return;

        ApiEndpoint::create(
            array(
                'name' => 'get-user-claims-get',
                'active' => true,
                'api_id' => $users->id,
                'route' => '/api/v1/users/info',
                'http_method' => 'GET',
                'allow_cors' => true,
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-user-claims-post',
                'active' => true,
                'api_id' => $users->id,
                'route' => '/api/v1/users/info',
                'http_method' => 'POST',
                'allow_cors' => true,
            )
        );

        $profile_scope = ApiScope::where('name', '=', 'profile')->first();
        $email_scope = ApiScope::where('name', '=', 'email')->first();
        $address_scope = ApiScope::where('name', '=', 'address')->first();


        $get_user_info_endpoint = ApiEndpoint::where('name', '=', 'get-user-claims-get')->first();
        $get_user_info_endpoint->scopes()->attach($profile_scope->id);
        $get_user_info_endpoint->scopes()->attach($email_scope->id);
        $get_user_info_endpoint->scopes()->attach($address_scope->id);

        $get_user_info_endpoint = ApiEndpoint::where('name', '=', 'get-user-claims-post')->first();
        $get_user_info_endpoint->scopes()->attach($profile_scope->id);
        $get_user_info_endpoint->scopes()->attach($email_scope->id);
        $get_user_info_endpoint->scopes()->attach($address_scope->id);
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        //
    }

}
