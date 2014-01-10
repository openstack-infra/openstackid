<?php

namespace services\oauth2;

use oauth2\IResourceServerContext;

class ResourceServerContext implements IResourceServerContext {

    private $auth_context;

    public function getCurrentScope()
    {
        return $this->auth_context['scope'];
    }

    public function getCurrentAccessToken()
    {
        return $this->auth_context['access_token'];
    }


    public function getCurrentAccessTokenLifetime()
    {
        return $this->auth_context['expires_in'];
    }

    public function getCurrentClientId()
    {
        return $this->auth_context['client_id'];
    }

    public function setAuthorizationContext($auth_context)
    {
        $this->auth_context = $auth_context;
    }

}