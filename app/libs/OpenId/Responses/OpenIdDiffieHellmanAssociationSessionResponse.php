<?php namespace OpenId\Responses;
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
use OpenId\OpenIdProtocol;
/**
 * Class OpenIdDiffieHellmanAssociationSessionResponse
 * @package OpenId\Responses
 */
class OpenIdDiffieHellmanAssociationSessionResponse extends OpenIdAssociationSessionResponse
{
    /**
     * OpenIdDiffieHellmanAssociationSessionResponse constructor.
     * @param string $assoc_handle
     * @param string $session_type
     * @param string $assoc_type
     * @param int $expires_in
     * @param string $server_public
     * @param string $enc_mac_key
     */
    public function __construct($assoc_handle, $session_type, $assoc_type, $expires_in, $server_public, $enc_mac_key)
    {
        parent::__construct($assoc_handle, $session_type, $assoc_type, $expires_in);
        $this[OpenIdProtocol::OpenIdProtocol_DHServerPublic] = $server_public;
        $this[OpenIdProtocol::OpenIdProtocol_DHEncMacKey] = $enc_mac_key;
    }
} 