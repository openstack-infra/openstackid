<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/3/13
 * Time: 10:40 AM
 */

namespace oauth2\grant_types;

use oauth2\requests\OAuth2Request;

interface IGrantType {

    public function canHandle(OAuth2Request $request);
    public function handle(OAuth2Request $request);
    public function completeFlow(OAuth2Request $request);
    public function getResponseType();
    public function getType();
}