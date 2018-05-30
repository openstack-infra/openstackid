<?php
use OAuth2\OAuth2Protocol;
use Models\OAuth2\Api;
use Models\OAuth2\ApiScope;
use Illuminate\Database\Seeder;

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


        ApiScope::create(
            array(
                'name'               => OAuth2Protocol::OpenIdConnect_Scope,
                'short_description'  => 'OpenId Connect Protocol',
                'description'        => 'OpenId Connect Protocol',
                'api_id'             => null,
                'system'             => true,
                'default'            => true,
                'active'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => OAuth2Protocol::OfflineAccess_Scope,
                'short_description'  => 'allow to emit refresh tokens (offline access without user presence)',
                'description'        => 'allow to emit refresh tokens (offline access without user presence)',
                'api_id'             => null,
                'system'             => true,
                'default'            => true,
                'active'             => true,
            )
        );

    }

}