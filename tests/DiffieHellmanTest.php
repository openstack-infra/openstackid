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
use OpenId\Helpers\AssocHandleGenerator;
use OpenId\Helpers\OpenIdCryptoHelper;
use OpenId\Requests\OpenIdDHAssociationSessionRequest;
use Zend\Crypt\PublicKey\DiffieHellman;
use Tests\TestCase;
/**
 * Class DiffieHellmanTest
 */
class DiffieHellmanTest extends TestCase
{

    public function testDefaultDHParams()
    {
        $g = OpenIdDHAssociationSessionRequest::DH_G;
        $p = OpenIdDHAssociationSessionRequest::DH_P;

        $g_bin = pack('H*', $g);
        $p_bin = pack('H*', $p);

        $g_number = OpenIdCryptoHelper::convert($g_bin, DiffieHellman::FORMAT_BINARY, DiffieHellman::FORMAT_NUMBER);
        $p_number = OpenIdCryptoHelper::convert($p_bin, DiffieHellman::FORMAT_BINARY, DiffieHellman::FORMAT_NUMBER);

        $this->assertTrue($g_number == '2');
        $this->assertTrue($p_number == '155172898181473697471232257763715539915724801966915404479707795314057629378541917580651227423698188993727816152646631438561595825688188889951272158842675419950341258706556549803580104870537681476726513255747040765857479291291572334510643245094715007229621094194349783925984760375594985848253359305585439638443');
    }

    public function testAssocHandlerGenerator()
    {
        $handler = AssocHandleGenerator::generate(32);
        $this->assertTrue(strlen($handler) == 32);
    }

} 