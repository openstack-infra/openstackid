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
use Guzzle\Http\Client as HttpClient;
use utils\http\HttpContentType;

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

        $client = new HttpClient;
        $client->setSslVerification(false);
        $request  = $client->get($jwk_set_uri);
        $response = $request->send();
        if($response->getStatusCode() !== 200) return null;
        $content_type = $response->getHeader('content-type');
        if(is_null($content_type)) return null;
        if(!$content_type->hasValue(HttpContentType::Json)) return null;
        return JWKSet::fromJson($response->getBody());
    }
}