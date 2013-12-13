<?php

class ApiSeeder extends Seeder {
    public function run()
    {
        DB::table('oauth2_api')->delete();

        Api::create(
            array(
                'name'            => 'test api user activities',
                'endpoint_url'    => 'https://www.test.com/users/activities',
                'logo'            =>  null,
                'active'          => 'true',
            )
        );


    }

} 