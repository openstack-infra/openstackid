<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 2:29 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\extensions;

use openid\requests\OpenIdRequest;
use openid\requests\contexts\RequestContext;
use openid\responses\OpenIdResponse;
use openid\responses\contexts\ResponseContext;

interface IOpenIdExtension {

    public function apply(OpenIdRequest $request,RequestContext $context);
    public function transform(OpenIdRequest $request,OpenIdResponse $response ,ResponseContext $context);
}