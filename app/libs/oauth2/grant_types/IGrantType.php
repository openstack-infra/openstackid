<?php


namespace oauth2\grant_types;

use oauth2\requests\OAuth2Request;

interface IGrantType {

    public function canHandle(OAuth2Request $request);
    public function handle(OAuth2Request $request);
    public function completeFlow(OAuth2Request $request);
    public function getType();
    public function getResponseType();
    public function buildTokenRequest(OAuth2Request $request);
}