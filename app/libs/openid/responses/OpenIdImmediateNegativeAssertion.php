<?php

namespace openid\responses;

use openid\OpenIdProtocol;

/**
 * Class OpenIdImmediateNegativeAssertion
 * implements http://openid.net/specs/openid-authentication-2_0.html#negative_assertions
 * Negative Assertions
 * In Response to Immediate Requests
 * @package openid\responses
 */
class OpenIdImmediateNegativeAssertion extends OpenIdIndirectResponse
{

    public function __construct($return_url = null)
    {
        parent::__construct();
        $this->setMode(OpenIdProtocol::SetupNeededMode);
        if (!is_null($return_url) && !empty($return_url)) {
            $this->setReturnTo($return_url);
        }
    }
}