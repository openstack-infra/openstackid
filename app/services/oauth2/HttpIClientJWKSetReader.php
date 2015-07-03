<?php
/**
 * Copyright 2015 OpenStack Foundation
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

namespace services\oauth2;


use jwk\IJWKSet;
use jwk\impl\JWKSet;
use oauth2\models\IClient;
use oauth2\services\IClientJWKSetReader;
use GuzzleHttp\Client as HttpClient;
/**
 * Class HttpIClientJWKSetReader
 * @package services\oauth2
 */
final class HttpIClientJWKSetReader implements IClientJWKSetReader
{

    /**
     * @param IClient $client
     * @return IJWKSet
     */
    public function read(IClient $client)
    {
        $jwk_set_uri = $client->getJWKSUri();
        if(empty($jwk_set_uri)) return null;

        $client = new HttpClient();
        $res    = $client->get($jwk_set_uri);

        if($res->getStatusCode() !== 200) return null;

        if($res->getHeader('content-type') !== 'application/json; charset=utf8') return null;

        return JWKSet::fromJson($res->getBody());
    }
}