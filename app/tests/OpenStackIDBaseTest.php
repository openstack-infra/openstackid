<?php
/**
 * Copyright 2015 Openstack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

/**
 * Class OpenStackIDBaseTest
 */
abstract class OpenStackIDBaseTest extends TestCase {

    protected function prepareForTests()
    {
        DB::table('banned_ips')->delete();
        DB::table('user_exceptions_trail')->delete();
        DB::table('server_configuration')->delete();
        DB::table('server_extensions')->delete();

        DB::table('oauth2_client_api_scope')->delete();
        DB::table('oauth2_client_authorized_uri')->delete();
        DB::table('oauth2_access_token')->delete();
        DB::table('oauth2_refresh_token')->delete();
        DB::table('oauth2_client')->delete();

        DB::table('openid_trusted_sites')->delete();
        DB::table('openid_associations')->delete();
        DB::table('openid_users')->delete();

        DB::table('oauth2_api_endpoint_api_scope')->delete();
        DB::table('oauth2_api_endpoint')->delete();
        DB::table('oauth2_api_scope')->delete();
        DB::table('oauth2_api')->delete();
        DB::table('oauth2_resource_server')->delete();

        parent::prepareForTests();
    }
}