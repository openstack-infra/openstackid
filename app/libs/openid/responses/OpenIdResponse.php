<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 10:25 AM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\responses;
use openid\OpenIdProtocol;
use utils\http\HttpResponse;
use openid\exceptions\InvalidOpenIdMessageMode;

abstract class OpenIdResponse extends HttpResponse
{

    public function __construct($http_code, $content_type)
    {
        parent::__construct($http_code, $content_type);
    }

    protected function setMode($mode)
    {
        if (!OpenIdProtocol::isValidMode($mode))
            throw new InvalidOpenIdMessageMode(sprintf(OpenIdErrorMessages::InvalidOpenIdMessageModeMessage, $mode));
        $this->container[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Mode)] = $mode;;
    }

}