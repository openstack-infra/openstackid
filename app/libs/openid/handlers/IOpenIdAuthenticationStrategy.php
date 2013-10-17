<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 3:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\handlers;
use openid\requests\OpenIdAuthenticationRequest;
use openid\requests\contexts\RequestContext;
interface IOpenIdAuthenticationStrategy {

    public function doLogin(OpenIdAuthenticationRequest $request,RequestContext $context);

    public function doConsent(OpenIdAuthenticationRequest $request,RequestContext $context);
}