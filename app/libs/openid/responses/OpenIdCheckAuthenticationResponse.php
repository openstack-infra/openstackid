<?php

namespace openid\responses;

use openid\OpenIdProtocol;

/**
 * Class OpenIdCheckAuthenticationResponse
 * @package openid\responses
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