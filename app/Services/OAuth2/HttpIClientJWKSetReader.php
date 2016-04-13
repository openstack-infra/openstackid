<?php namespace Services\OAuth2;
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
use jwk\IJWKSet;
use jwk\impl\JWKSet;
use OAuth2\Models\IClient;
use OAuth2\Services\IClientJWKSetReader;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException as HttpRequestException;
use Utils\Http\HttpContentType;
use Log;
/**
 * Class HttpIClientJWKSetReader
 * @package Services\OAuth2
 */
final class HttpIClientJWKSetReader implements IClientJWKSetReader
{

    /**
     * @param IClient $client
     * @return IJWKSet
     */
    public function read(IClient $client)
    {
        try {
            $jwk_set_uri = $client->getJWKSUri();
            if (empty($jwk_set_uri)) return null;

            $client = new HttpClient([
                'defaults' => [
                    'timeout' => Config::get('curl.timeout', 60),
                    'allow_redirects' => Config::get('curl.allow_redirects', false),
                    'verify' => Config::get('curl.verify_ssl_cert', true)
                ]
            ]);

            $response  = $client->get($jwk_set_uri);
            if ($response->getStatusCode() !== 200) return null;
            $content_type = $response->getHeader('content-type');
            if (is_null($content_type)) return null;
            if (!$content_type->hasValue(HttpContentType::Json)) return null;
            return JWKSet::fromJson($response->getBody());
        }
        catch (HttpRequestException $ex)
        {
            Log::warning($ex->getMessage());
            return null;
        }
    }
}