<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 2:43 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\extensions\implementations;
use openid\extensions\IOpenIdExtension;
use openid\requests\contexts\RequestContext;
use openid\requests\OpenIdRequest;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdResponse;

class OpenIdOAuthExtension implements IOpenIdExtension {

    public function apply(OpenIdRequest $request, RequestContext $context)
    {
        // TODO: Implement apply() method.
    }

    public function transform(OpenIdRequest $request, OpenIdResponse $response, ResponseContext $context)
    {
        // TODO: Implement transform() method.
    }
}