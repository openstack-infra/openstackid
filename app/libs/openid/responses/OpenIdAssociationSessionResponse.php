<?php

namespace openid\responses;

use openid\OpenIdProtocol;

abstract class OpenIdAssociationSessionResponse extends OpenIdDirectResponse
{

    /** Common Response Parameters
     * @param $assoc_handle
     *        The association handle is used as a key to refer to this association in subsequent messages.
     *        A string 255 characters or less in length. It MUST consist only of ASCII characters in the
     *        range 33-126 inclusive (printable non-whitespace characters).
     * @param $session_type
     *        The value of the "openid.session_type" parameter from the request. If the OP is unwilling
     *        or unable to support this association type, it MUST return an unsuccessful response.
     * @param $assoc_type
     *        The value of the "openid.assoc_type" parameter from the request. If the OP is unwilling or
     *        unable to support this association type, it MUST return an unsuccessful response.
     * @param $expires_in
     *        The lifetime, in seconds, of this association. The Relying Party MUST NOT use
     *        the association after this time has passed.
     *        An integer, represented in base 10 ASCII.
     */
    public function __construct($assoc_handle, $session_type, $assoc_type, $expires_in)
    {
        parent::__construct();
        $this[OpenIdProtocol::OpenIDProtocol_AssocHandle] = $assoc_handle;
        $this[OpenIdProtocol::OpenIDProtocol_SessionType] = $session_type;
        $this[OpenIdProtocol::OpenIDProtocol_AssocType] = $assoc_type;
        $this[OpenIdProtocol::OpenIdProtocol_ExpiresIn] = $expires_in;
    }

} 