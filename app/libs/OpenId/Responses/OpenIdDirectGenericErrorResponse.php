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
 * Class OpenIdDirectGenericErrorResponse
 * implements 5.1.2.2. Error Responses
 * @package OpenId\Responses
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