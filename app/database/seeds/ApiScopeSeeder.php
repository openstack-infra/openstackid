<?php

class ApiScopeSeeder extends Seeder {

    public function run()
    {
        DB::table('oauth2_api_scope')->delete();

        $api = Api::where('name','=','test api user activities')->first();

        ApiScope::create(
            array(
                'name'               => 'https://www.test.com/users/activities.read',
                'short_description'  => 'User Activities Read Access',
                'description'        =>  'User Activities Read Access',
                'api_id'             => $api->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => 'https://www.test.com/users/activities.write',
                'short_description'  => 'User Activities Write Access',
                'description'        =>  'User Activities Write Access',
                'api_id'             => $api->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => 'https://www.test.com/users/activities.read.write',
                'short_description'  => 'User Activities Read/Write Access',
                'description'        =>  'User Activities Read/Write Access',
                'api_id'             => $api->id,
            )
        );
    }

} 