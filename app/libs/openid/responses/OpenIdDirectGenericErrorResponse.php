<?php

namespace openid\responses;

use openid\OpenIdProtocol;

/**
 * Class OpenIdDirectGenericErrorResponse
 * implements 5.1.2.2.  Error Responses
 * @package openid\responses
 */
class OpenIdDirectGenericErrorResponse extends OpenIdDirectResponse
{
    /**
     * @param $error :  A human-readable message indicating the cause of the error.
     * @param null $contact : (optional) Contact address for the administrator of the sever.
     *                        The contact address may take any form, as it is intended to be
     *                        displayed to a person.
     * @param null $reference :  (optional) A reference token, such as a support ticket number
     *                          or a URL to a news blog, etc.
     */
    public function __construct($error, $contact = null, $reference = null)
    {
        parent::__construct();
        $this->setHttpCode(self::HttpErrorResponse);
        $this[OpenIdProtocol::OpenIDProtocol_Error] = $error;
        //opt values
        if (!is_null($contact))
            $this[OpenIdProtocol::OpenIDProtocol_Contact] = $contact;
        if (!is_null($reference))
            $this[OpenIdProtocol::OpenIDProtocol_Reference] = $reference;
    }
}