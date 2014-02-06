<?php
class ApiEndpointSeeder extends Seeder
{

    public function run()
    {

        DB::table('oauth2_api_endpoint_api_scope')->delete();
        DB::table('oauth2_api_endpoint')->delete();
        $this->seedUsersEndpoints();
    }

    private function seedUsersEndpoints()
    {
        $users = Api::where('name', '=', 'users')->first();
        // endpoints scopes

        ApiEndpoint::create(
            array(
                'name' => 'get-user-info',
                'active' => true,
                'api_id' => $users->id,
                'route' => '/api/v1/users/me',
                'http_method' => 'GET'
            )
        );
        $profile_scope = ApiScope::where('name', '=', 'profile')->first();
        $email_scope = ApiScope::where('name', '=', 'email')->first();
        $address_scope = ApiScope::where('name', '=', 'address')->first();

        $get_user_info_endpoint = ApiEndpoint::where('name', '=', 'get-user-info')->first();
        $get_user_info_endpoint->scopes()->attach($profile_scope->id);
        $get_user_info_endpoint->scopes()->attach($email_scope->id);
        $get_user_info_endpoint->scopes()->attach($address_scope->id);
    }


}