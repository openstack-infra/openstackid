<?php

namespace openid\responses;

use openid\helpers\OpenIdUriHelper;
use openid\OpenIdProtocol;
use openid\requests\OpenIdRequest;

class OpenIdIndirectGenericErrorResponse extends OpenIdIndirectResponse
{

    public function __construct($error, $contact = null, $reference = null, OpenIdRequest $request = null)
    {
        parent::__construct();
        $this->setHttpCode(self::HttpErrorResponse);
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Error)] = $error;
        //opt values
        if (!is_null($contact))
            $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Contact)] = $contact;
        if (!is_null($reference))
            $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Reference)] = $reference;

        if (!is_null($request)) {
            $return_to = $request->getParam(OpenIdProtocol::OpenIDProtocol_ReturnTo);
            if (!is_null($return_to) && !empty($return_to) && OpenIdUriHelper::checkReturnTo($return_to))
                $this->setReturnTo($return_to);
        }
    }

}