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
use OpenId\Models\Association;
use OpenId\Models\IAssociation;
use OpenId\OpenIdProtocol;
/**
 * Class AssociationFactory
 * Singleton Factory that creates OpenId Associations
 * @package OpenId\Helpers
 */
final class AssociationFactory
{

    /**
     * @var AssociationFactory
     */
    private static $instance = null;

    private function __construct(){}

    /**
     * @return null|AssociationFactory
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new AssociationFactory();
        }
        return self::$instance;
    }

    /**
     * @param $realm
     * @param $lifetime
     * @return IAssociation
     */
    public function buildPrivateAssociation($realm, $lifetime)
    {
        return $this->buildAssociation(IAssociation::TypePrivate, OpenIdProtocol::SignatureAlgorithmHMAC_SHA256, $lifetime, $realm);
    }

    /**
     * @param $mac_function
     * @param $lifetime
     * @return IAssociation
     */
    public function buildSessionAssociation($mac_function, $lifetime)
    {
        return $this->buildAssociation(IAssociation::TypeSession, $mac_function, $lifetime, null);
    }

    /**
     * @param string $type
     * @param string $mac_function
     * @param int $lifetime
     * @param string $realm
     * @return IAssociation
     */
    private function buildAssociation($type, $mac_function, $lifetime, $realm)
    {
        $new_secret = OpenIdCryptoHelper::generateSecret($mac_function);
        $new_handle = AssocHandleGenerator::generate();
        $expires_in = intval($lifetime);
        $issued     = gmdate("Y-m-d H:i:s", time());

        return new Association($new_handle, $new_secret, $mac_function, $expires_in, $issued, $type, $realm);
    }

    private function __clone()
    {
    }
} 