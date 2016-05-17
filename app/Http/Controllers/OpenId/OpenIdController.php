<?php namespace App\Http\Controllers\OpenId;
/**
 * Copyright 2015 Openstack Foundation
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
use Illuminate\Support\Facades\Request;
use OpenId\Xrds\XRDSDocumentBuilder;
use App\Http\Controllers\Controller;

/**
 * Class OpenIdController
 * @package App\Http\Controllers\OpenId
 */
abstract class OpenIdController extends Controller {

    /**
     * @return bool
     */
    protected function isDiscoveryRequest(){
        //This field contains a semicolon-separated list of representation schemes
        //which will be accepted in the response to this request.
        $accept = Request::header('Accept');
        return strstr($accept, XRDSDocumentBuilder::ContentType) !== false;
    }

    /**
     * @param $response
     */
    protected function setDiscoveryResponseType($response){
        $response->header('Content-Type', implode('; ', array(XRDSDocumentBuilder::ContentType, XRDSDocumentBuilder::Charset)));
    }
}