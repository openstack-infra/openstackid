<?php

namespace oauth2;


interface IResourceServerContext {

    public function getCurrentScope();
    public function getCurrentAccessToken();
    public function getCurrentAccessTokenLifetime();
    public function getCurrentClientId();
    public function setAuthorizationContext($auth_context);
} 