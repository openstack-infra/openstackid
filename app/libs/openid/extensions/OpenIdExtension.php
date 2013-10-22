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

abstract class OpenIdExtension {
    protected $namespace;
    protected $name;
    protected $description;

    public function __construct($name,$namespace,$description){
        $this->namespace    = $namespace;
        $this->name         = $name;
        $this->description  = $description;
    }

    public function getNamespace(){
        return $this->namespace;
    }

    /**
     * @param OpenIdRequest $request
     * @param RequestContext $context
     * @return mixed
     * @throws InvalidOpenIdMessageException
     */
    abstract public function parseRequest(OpenIdRequest $request,RequestContext $context);
    abstract public function prepareResponse(OpenIdRequest $request,OpenIdResponse $response ,ResponseContext $context);
}