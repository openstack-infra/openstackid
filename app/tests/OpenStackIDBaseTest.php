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
        if (Schema::hasTable('banned_ips'))
            DB::table('banned_ips')->delete();
        if (Schema::hasTable('user_exceptions_trail'))
            DB::table('user_exceptions_trail')->delete();
        if (Schema::hasTable('server_configuration'))
            DB::table('server_configuration')->delete();
        if (Schema::hasTable('server_extensions'))
            DB::table('server_extensions')->delete();
        if (Schema::hasTable('oauth2_client_api_scope'))
            DB::table('oauth2_client_api_scope')->delete();
        if (Schema::hasTable('oauth2_client_authorized_uri'))
            DB::table('oauth2_client_authorized_uri')->delete();
        if (Schema::hasTable('oauth2_access_token'))
            DB::table('oauth2_access_token')->delete();
        if (Schema::hasTable('oauth2_refresh_token'))
            DB::table('oauth2_refresh_token')->delete();
        if (Schema::hasTable('oauth2_client'))
            DB::table('oauth2_client')->delete();
        if (Schema::hasTable('openid_trusted_sites'))
            DB::table('openid_trusted_sites')->delete();
        if (Schema::hasTable('openid_associations'))
            DB::table('openid_associations')->delete();
        if (Schema::hasTable('openid_users'))
            DB::table('openid_users')->delete();
        if (Schema::hasTable('oauth2_api_endpoint_api_scope'))
            DB::table('oauth2_api_endpoint_api_scope')->delete();
        if (Schema::hasTable('oauth2_api_endpoint'))
            DB::table('oauth2_api_endpoint')->delete();
        if (Schema::hasTable('oauth2_api_scope'))
            DB::table('oauth2_api_scope')->delete();
        if (Schema::hasTable('oauth2_api'))
            DB::table('oauth2_api')->delete();
        if (Schema::hasTable('oauth2_resource_server'))
            DB::table('oauth2_resource_server')->delete();

        parent::prepareForTests();
    }
}