<?php namespace Utils;
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

/**
 * Class JsonUtils
 * @see http://json.org/
 * @package Utils
 */
abstract class JsonUtils
{
    /**
     * A string is a sequence of zero or more Unicode characters, wrapped in double quotes, using backslash escapes.
     * A character is represented as a single character string. A string is very much like a C or Java string.
     * @param string $value
     * @return string
     */
    public static function toJsonString($value)
    {
        return $value;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function toJsonBoolean($value)
    {
        if(empty($value)) return false;
        return boolval($value);
    }

    /**
     * @param string $value
     * @return int|null
     */
    public static function toJsonInt($value)
    {
        if(empty($value)) return null;
        return intval($value);
    }

    /**
     * @param string $value
     * @return float|null
     */
    public static function toJsonFloat($value)
    {
        if(empty($value)) return null;
        return floatval(number_format(floatval($value),2));
    }

    /**
     * @param string $value
     * @return int
     */
    public static function toEpoch($value)
    {
        if(empty($value)) return 0;
        $datetime = new \DateTime($value);
        return $datetime->getTimestamp();
    }
}