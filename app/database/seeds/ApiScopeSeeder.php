<?php

class ApiScopeSeeder extends Seeder {

    public function run()
    {
        DB::table('oauth2_api_scope')->delete();

        $resource_server_api = Api::where('name','=','resource server api')->first();

        $current_realm = Config::get('app.url');

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/read',$current_realm),
                'short_description'  => 'Resource Server Read Access',
                'description'        => 'Resource Server Read Access',
                'api_id'             => $resource_server_api->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/write',$current_realm),
                'short_description'  => 'Resource Server Write Access',
                'description'        => 'Resource Server Write Access',
            )
        );


    }

} 