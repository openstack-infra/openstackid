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
use OpenId\Helpers\OpenIdUriHelper;
use OpenId\OpenIdProtocol;
use OpenId\Requests\OpenIdRequest;
/**
 * Class OpenIdIndirectGenericErrorResponse
 * @package OpenId\Responses
 */
class OpenIdIndirectGenericErrorResponse extends OpenIdIndirectResponse
{
    /**
     * OpenIdIndirectGenericErrorResponse constructor.
     * @param $error
     * @param null $contact
     * @param null $reference
     * @param OpenIdRequest|null $request
     */
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