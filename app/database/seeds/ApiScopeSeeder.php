<?php

/**
 * Class ApiScopeSeeder
 */
class ApiScopeSeeder extends Seeder {


    public function run()
    {
        DB::table('oauth2_api_endpoint_api_scope')->delete();
        DB::table('oauth2_client_api_scope')->delete();
        DB::table('oauth2_api_scope')->delete();
        $this->seedUsersScopes();
        $this->seedPublicCloudScopes();
        $this->seedPrivateCloudScopes();
        $this->seedConsultantScopes();
    }

    private function seedUsersScopes(){

        $users    = Api::where('name','=','users')->first();

        ApiScope::create(
            array(
                'name'               => 'profile',
                'short_description'  => 'Allows access to your profile info.',
                'description'        => 'This scope value requests access to the End-Users default profile Claims, which are: name, family_name, given_name, middle_name, nickname, preferred_username, profile, picture, website, gender, birthdate, zoneinfo, locale, and updated_at.',
                'api_id'             => $users->id,
                'system'             => false,
            )
        );

        ApiScope::create(
            array(
                'name'               => 'email',
                'short_description'  => 'Allows access to your email info.',
                'description'        => 'This scope value requests access to the email and email_verified Claims.',
                'api_id'             => $users->id,
                'system'             => false,
            )
        );

        ApiScope::create(
            array(
                'name'               => 'address',
                'short_description'  => 'Allows access to your Address info.',
                'description'        => 'This scope value requests access to the address Claim.',
                'api_id'             => $users->id,
                'system'             => false,
            )
        );

    }

    private function seedPublicCloudScopes(){

        $current_realm = Config::get('app.url');
        $public_clouds    = Api::where('name','=','public-clouds')->first();

        ApiScope::create(
            array(
                'name'               => sprintf('%s/public-clouds/read',$current_realm),
                'short_description'  => 'Get Public Clouds',
                'description'        => 'Grants read only access for Public Clouds',
                'api_id'             => $public_clouds->id,
                'system'             => false,
            )
        );
    }

    private function seedPrivateCloudScopes(){

        $current_realm  = Config::get('app.url');
        $private_clouds = Api::where('name','=','private-clouds')->first();

        ApiScope::create(
            array(
                'name'               => sprintf('%s/private-clouds/read',$current_realm),
                'short_description'  => 'Get Private Clouds',
                'description'        => 'Grants read only access for Private Clouds',
                'api_id'             => $private_clouds->id,
                'system'             => false,
            )
        );
    }


    private function seedConsultantScopes(){

        $current_realm  = Config::get('app.url');
        $consultants = Api::where('name','=','consultants')->first();

        ApiScope::create(
            array(
                'name'               => sprintf('%s/consultants/read',$current_realm),
                'short_description'  => 'Get Consultants',
                'description'        => 'Grants read only access for Consultants',
                'api_id'             => $consultants->id,
                'system'             => false,
            )
        );
    }

}