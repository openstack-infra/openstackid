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
use OpenId\Exceptions\OpenIdCryptoException;
use OpenId\OpenIdProtocol;
use Zend\Crypt\PublicKey\DiffieHellman;
use Zend\Math\Rand;
use Zend\Math;
use Zend\Math\BigInteger\BigInteger;
use Exception;
/**
 * Class OpenIdCryptoHelper
 * @package OpenId\Helpers
 */
final class OpenIdCryptoHelper
{

    /**
     * @var array
     */
    private static $signature_algorithms = array
    (
        OpenIdProtocol::SignatureAlgorithmHMAC_SHA1    => "sha1",
        OpenIdProtocol::AssociationSessionTypeDHSHA1   => "sha1",
        OpenIdProtocol::SignatureAlgorithmHMAC_SHA256  => "sha256",
        OpenIdProtocol::AssociationSessionTypeDHSHA256 => "sha256",
    );

    /**
     * @param $number
     * @param string $inputFormat
     * @param string $outputFormat
     * @return string
     */
    public static function convert($number, $inputFormat = DiffieHellman::FORMAT_NUMBER, $outputFormat = DiffieHellman::FORMAT_BINARY)
    {
        $math = BigInteger::factory();
        if ($inputFormat == $outputFormat) {
            return $number;
        }
        // convert to number
        switch ($inputFormat) {
            case DiffieHellman::FORMAT_BINARY:
            case DiffieHellman::FORMAT_BTWOC:
                $number = $math->binToInt($number);
                break;
            case DiffieHellman::FORMAT_NUMBER:
            default:
                // do nothing
                break;
        }

        // convert to output format
        switch ($outputFormat) {
            case DiffieHellman::FORMAT_BINARY:
                return $math->intToBin($number);
                break;
            case DiffieHellman::FORMAT_BTWOC:
                return $math->intToBin($number, true);
                break;
            case DiffieHellman::FORMAT_NUMBER:
            default:
                return $number;
                break;
        }
    }

    /**
     * @param $func
     * @return string
     */
    public static function generateSecret($func)
    {
        if ($func == OpenIdProtocol::SignatureAlgorithmHMAC_SHA1) {
            $macLen = 20; /* 160 bit */
        } else if ($func == OpenIdProtocol::SignatureAlgorithmHMAC_SHA256) {
            $macLen = 32; /* 256 bit */
        } else {
            $macLen = 20;/* 160 bit */
        }
        $bytes = self::randomBytes($macLen);
        return $bytes;
    }

    /**
     * Produces string of random byte of given length.
     *
     * @param integer $len length of requested string
     * @return string RAW random binary string
     */
    static public function randomBytes($len)
    {
        return Rand::getBytes($len, true);
    }

    /**
     * @param $macFunc
     * @param $data
     * @param $secret
     * @return string
     * @throws OpenIdCryptoException
     */
    static public function computeHMAC($macFunc, $data, $secret)
    {
        if (!isset(self::$signature_algorithms[$macFunc]))
            throw new OpenIdCryptoException(sprintf(OpenIdErrorMessages::InvalidMacFunctionMessage, $macFunc));
        $macFunc = self::$signature_algorithms[$macFunc];

        if (function_exists('hash_hmac')) {
            return hash_hmac($macFunc, $data, $secret, 1);
        } else {
            if (self::strlen($secret) > 64) {
                $secret = self::digest($macFunc, $secret);
            }
            $secret = str_pad($secret, 64, chr(0x00));
            $ipad = str_repeat(chr(0x36), 64);
            $opad = str_repeat(chr(0x5c), 64);
            $hash1 = self::digest($macFunc, ($secret ^ $ipad) . $data);
            return self::digest($macFunc, ($secret ^ $opad) . $hash1);
        }
    }

    /**
     * Returns length of binary string in bytes
     *
     * @param string $str
     * @return int the string length
     */
    static public function strlen($str)
    {
        if (extension_loaded('mbstring') &&
            (((int)ini_get('mbstring.func_overload')) & 2)
        ) {
            return mb_strlen($str, 'latin1');
        } else {
            return strlen($str);
        }
    }

    /**
     * Generates a hash value (message digest) according to given algorithm.
     * It returns RAW binary string.
     *
     * This is a wrapper function that uses one of available internal function
     * dependent on given PHP configuration. It may use various functions from
     *  ext/openssl, ext/hash, ext/mhash or ext/standard.
     *
     * @param string $func digest algorithm
     * @param string $data data to sign
     * @return string RAW digital signature
     * @throws Exception
     */
    static public function digest($func, $data)
    {
        if (!isset(self::$signature_algorithms[$func]))
            throw new OpenIdCryptoException(sprintf(OpenIdErrorMessages::InvalidMacFunctionMessage, $func));
        $func = self::$signature_algorithms[$func];

        if (function_exists('openssl_digest')) {
            return openssl_digest($data, $func, true);
        } else if (function_exists('hash')) {
            return hash($func, $data, true);
        } else if ($func == 'sha1') {
            return sha1($data, true);
        } else if ($func == 'sha256') {
            if (function_exists('mhash')) {
                return mhash(MHASH_SHA256, $data);
            }
        }
        throw new \Exception('Unsupported digest algorithm "' . $func . '".');
    }

    /**
     * Takes an arbitrary precision integer and returns its shortest big-endian
     * two's complement representation.
     *
     * Arbitrary precision integers MUST be encoded as big-endian signed two's
     * complement binary strings. Henceforth, "btwoc" is a function that takes
     * an arbitrary precision integer and returns its shortest big-endian two's
     * complement representation. All integers that are used with
     * Diffie-Hellman Key Exchange are positive. This means that the left-most
     * bit of the two's complement representation MUST be zero. If it is not,
     * implementations MUST add a zero byte at the front of the string.
     *
     * @param string $str binary representation of arbitrary precision integer
     * @return string big-endian signed representation
     */
    static public function btwoc($str)
    {
        if (ord($str[0]) > 127) {
            return "\0" . $str;
        }
        return $str;
    }
}