<?php

/**
 * Class DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        $this->call('OpenIdExtensionsSeeder');
        $this->call('ServerConfigurationSeeder');

        DB::table('oauth2_api_endpoint_api_scope')->delete();
        DB::table('oauth2_client_api_scope')->delete();
        DB::table('oauth2_api_scope')->delete();
        DB::table('oauth2_api_endpoint')->delete();
        DB::table('oauth2_api')->delete();
        DB::table('oauth2_resource_server')->delete();

        $this->call('ResourceServerSeeder');
        $this->call('ApiSeeder');
        $this->call('ApiScopeSeeder');
        $this->call('ApiEndpointSeeder');
    }
}
