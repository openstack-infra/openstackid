<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 2:43 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\extensions\implementations;
use openid\extensions\OpenIdExtension;
use openid\requests\contexts\RequestContext;
use openid\requests\OpenIdRequest;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdResponse;

class OpenIdOAuthExtension extends OpenIdExtension {

    protected function populateProperties()
    {
        // TODO: Implement populateProperties() method.
    }

    public function parseRequest(OpenIdRequest $request, RequestContext $context)
    {
        // TODO: Implement parseRequest() method.
    }

    public function prepareResponse(OpenIdRequest $request, OpenIdResponse $response, ResponseContext $context)
    {
        // TODO: Implement prepareResponse() method.
    }
}