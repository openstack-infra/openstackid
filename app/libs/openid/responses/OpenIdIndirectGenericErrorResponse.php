<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 4:32 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\responses;


use openid\OpenIdProtocol;

class OpenIdIndirectGenericErrorResponse extends OpenIdIndirectResponse {

    public function __construct($error,$contact,$reference){
        parent::__construct();
        $this->setHttpCode(self::HttpErrorResponse);
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Error)] = $error;
        //opt values
        if(!is_null($contact))
            $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Contact)] = $contact;
        if(!is_null($reference))
            $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Reference)] = $reference;
    }

}