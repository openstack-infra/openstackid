<?php
/**
 * Copyright 2015 OpenStack Foundation
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

use OpenId\Xrds\XRDSDocumentBuilder;
use OpenId\Xrds\XRDSService;
/**
 * Class XRDSDocumentTest
 */
class XRDSDocumentTest extends TestCase
{
    public function testBuildDocument()
    {
        $services = array();
        array_push($services,
            new XRDSService(0, "http://specs.openid.net/auth/2.0/server", "https://dev.openstackid.com",
                array("http://openid.net/srv/ax/1.0", "http://specs.openid.net/extensions/pape/1.0")));
        $builder = new XRDSDocumentBuilder($services);
        $xrds = $builder->render();
        $this->assertTrue(!empty($xrds) && str_contains($xrds,
                "http://specs.openid.net/auth/2.0/server") && str_contains($xrds,
                "http://openid.net/srv/ax/1.0") && str_contains($xrds, "http://specs.openid.net/extensions/pape/1.0"));
    }
}