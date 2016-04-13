<?php namespace Utils\Http;
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

/**
 * Class HttpUtils
 * @package Utils\Http
 */
final class HttpUtils
{
    /**
     * @param string $schema
     * @return bool
     */
    public static function isCustomSchema($schema)
    {
        return !(self::isHttpSchema($schema) || strtolower($schema) === 'ftp' || strtolower($schema) === 'file');
    }

    /**
     * @param string $schema
     * @return bool
     */
    public static function isHttpSchema($schema)
    {
        return (strtolower($schema) === 'http' || self::isHttpsSchema($schema));
    }

    /**
     * @param string $schema
     * @return bool
     */
    public static function isHttpsSchema($schema){
        return (strtolower($schema) === 'https');
    }
}