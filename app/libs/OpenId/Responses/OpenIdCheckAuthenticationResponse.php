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
 * Class OpenIdCheckAuthenticationResponse
 * @package OpenId\Responses
 */
class OpenIdCheckAuthenticationResponse extends OpenIdDirectResponse {
    /**
     * 11.4.2.2. Response Parameters
     *  ns :As specified in Section 5.1.2.
     *  is_valid
     *  Value: "true" or "false"; asserts whether the signature of the verification
     *  request is valid.
     *  invalidate_handle
     *  Value: (optional) The "invalidate_handle" value sent in the verification request,
     *  if the OP confirms it is invalid.
     *  Description: If present in a verification response with "is_valid" set to "true",
     *  the Relying Party SHOULD remove the corresponding association from its store and
     *  SHOULD NOT send further authentication requests with this handle.
     *  Note: This two-step process for invalidating associations is necessary to prevent an attacker from invalidating an association at will by adding "invalidate_handle" parameters to an authentication response.
     * @param $is_valid
     * @param null $invalidate_handle
     */
    public function __construct($is_valid, $invalidate_handle = null)
    {
        parent::__construct();
        $this[OpenIdProtocol::OpenIDProtocol_IsValid] = $is_valid;
        if (!is_null($invalidate_handle) && !empty($invalidate_handle))
            $this[OpenIdProtocol::OpenIDProtocol_InvalidateHandle] = $invalidate_handle;
    }
} 