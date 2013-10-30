<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 10:20 AM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\services;


use openid\requests\OpenIdRequest;
use openid\OpenIdMessage;

interface IMementoOpenIdRequestService {

    /**
     * Save current OpenIdRequest till next request
     * @return bool
     */
    public function saveCurrentRequest();

    /** Retrieve last OpenIdMessage
     *  @return OpenIdMessage;
     */
    public function getCurrentRequest();

    public function clearCurrentRequest();
}