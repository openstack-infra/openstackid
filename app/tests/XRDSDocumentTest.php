<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 2:45 PM
 * To change this template use File | Settings | File Templates.
 */

use openid\XRDS\XRDSDocumentBuilder;
use openid\XRDS\XRDSService;

class XRDSDocumentTest  extends TestCase{
    public function testBuildDocument(){
        $services = array();
        array_push($services, new XRDSService(0,"http://specs.openid.net/auth/2.0/server","https://dev.openstackid.com",array("http://openid.net/srv/ax/1.0","http://specs.openid.net/extensions/pape/1.0")));
        $builder = new XRDSDocumentBuilder($services);
        $xrds = $builder->render();
        $this->assertTrue(!empty($xrds) && str_contains($xrds,"http://specs.openid.net/auth/2.0/server") && str_contains($xrds,"http://openid.net/srv/ax/1.0") && str_contains($xrds,"http://specs.openid.net/extensions/pape/1.0"));
    }
}