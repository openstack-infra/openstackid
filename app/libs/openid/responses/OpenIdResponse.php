<?php

namespace openid\responses;

use openid\OpenIdProtocol;
use utils\http\HttpResponse;
use openid\exceptions\InvalidOpenIdMessageMode;
use openid\helpers\OpenIdErrorMessages;

/**
 * Class OpenIdResponse
 * @package openid\responses
 */
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