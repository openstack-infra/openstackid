<?php namespace OpenId\Helpers;
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
use Zend\Math\Rand;
use InvalidArgumentException;
/**
 * Class AssocHandleGenerator
 * The association handle is used as a key to refer to this association in subsequent messages.
 * A string 255 characters or less in length. It MUST consist only of ASCII characters in the
 * range 33-126 inclusive (printable non-whitespace characters).
 * @package OpenId\Helpers
 */
final class AssocHandleGenerator
{

    const PrintableNonWhitespaceCharacters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.-_~';

    /**
     * @param int $len
     * @return string
     * @throws InvalidArgumentException
     */
    public static function generate($len = 32)
    {
        if ($len > 255)
            throw new InvalidArgumentException(sprintf("assoc handle len must be lower or equal to 255(%d)", $len));
        $new_assoc_handle = Rand::getString($len, self::PrintableNonWhitespaceCharacters, true);
        return $new_assoc_handle;
    }
} 