<?php
/**
 * Copyright 2016 OpenStack Foundation
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

return array
(
    'region_resource_server_lifetime'  => env('CACHE_REGION_RESOURCE_SERVER_LIFETIME', 60),
    'region_access_token_lifetime'     => env('CACHE_REGION_ACCESS_TOKEN_LIFETIME', 1140),
    'region_api_endpoint_lifetime'     => env('CACHE_REGION_API_ENDPOINT_LIFETIME', 1140),
    'region_api_scope_lifetime'        => env('CACHE_REGION_API_SCOPE_LIFETIME', 1140),
    'region_clients_lifetime'           => env('CACHE_REGION_CLIENT_LIFETIME', 1140),
    'region_refresh_token_lifetime'    => env('CACHE_REGION_REFRESH_TOKEN_LIFETIME', 1140),
    'region_white_listed_ip_lifetime'  => env('CACHE_REGION_WHITE_LISTED_IP_LIFETIME', 1140),
    'region_users_lifetime'            => env('CACHE_USERS_LIFETIME', 10),
);