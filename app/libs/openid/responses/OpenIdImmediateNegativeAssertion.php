<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 3:16 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\responses;


use openid\OpenIdProtocol;

class OpenIdImmediateNegativeAssertion extends OpenIdIndirectResponse{

    public function __construct(){
        parent::__construct();
        $this->setMode(OpenIdProtocol::SetupNeededMode);
    }
}