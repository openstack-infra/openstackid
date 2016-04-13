<?php namespace OpenId\Services;
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
use Utils\Model\Identifier;
use Utils\Services\UniqueIdentifierGenerator;
use Zend\Math\Rand;
/**
 * Class NonceUniqueIdentifierGenerator
 * @package OpenId\Services
 */
final class NonceUniqueIdentifierGenerator extends UniqueIdentifierGenerator {

    /*
    * MAY contain additional ASCII characters in the range 33-126 inclusive (printable non-whitespace characters), as necessary to make each response unique
    */
    const NoncePopulation = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    /**
    * Nonce Salt Length
    */
    const NonceSaltLength = 32;


    /**
     * @param Identifier $identifier
     * @return Identifier
     */
     protected function _generate(Identifier $identifier){

         $salt      = Rand::getString(self::NonceSaltLength, self::NoncePopulation, true);
         $date_part = false;
         do{ $date_part = gmdate('Y-m-d\TH:i:s\Z'); } while($date_part === false);
         $raw_nonce = $date_part. $salt;
         $identifier->setValue($raw_nonce);
         return $identifier;
     }

}