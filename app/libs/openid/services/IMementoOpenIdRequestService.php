<?php

namespace openid\services;


use openid\OpenIdMessage;

interface IMementoOpenIdRequestService
{

    /**
     * Save current OpenIdRequest till next request
     * @return bool
     */
    public function saveCurrentRequest();

    /** Retrieve last OpenIdMessage
     * @return OpenIdMessage;
     */
    public function getCurrentRequest();

    public function clearCurrentRequest();
}