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
 * Class OpenIdAssociationSessionResponse
 * @package OpenId\Responses
 */
abstract class OpenIdAssociationSessionResponse extends OpenIdDirectResponse
{

    /** Common Response Parameters
     * @param string $assoc_handle
     *        The association handle is used as a key to refer to this association in subsequent messages.
     *        A string 255 characters or less in length. It MUST consist only of ASCII characters in the
     *        range 33-126 inclusive (printable non-whitespace characters).
     * @param string $session_type
     *        The value of the "openid.session_type" parameter from the request. If the OP is unwilling
     *        or unable to support this association type, it MUST return an unsuccessful response.
     * @param string $assoc_type
     *        The value of the "openid.assoc_type" parameter from the request. If the OP is unwilling or
     *        unable to support this association type, it MUST return an unsuccessful response.
     * @param int $expires_in
     *        The lifetime, in seconds, of this association. The Relying Party MUST NOT use
     *        the association after this time has passed.
     *        An integer, represented in base 10 ASCII.
     */
    public function __construct($assoc_handle, $session_type, $assoc_type, $expires_in)
    {
        parent::__construct();
        $this[OpenIdProtocol::OpenIDProtocol_AssocHandle] = $assoc_handle;
        $this[OpenIdProtocol::OpenIDProtocol_SessionType] = $session_type;
        $this[OpenIdProtocol::OpenIDProtocol_AssocType]   = $assoc_type;
        $this[OpenIdProtocol::OpenIdProtocol_ExpiresIn]   = $expires_in;
    }

} 