<?php

namespace services\oauth2;

use oauth2\IResourceServerContext;

/**
 * Class ResourceServerContext
 * @package services\oauth2
 */
class ResourceServerContext implements IResourceServerContext {

    private $auth_context;

    /**
     * @return null|string
     */
    public function getCurrentScope()
    {
        return isset($this->auth_context['scope'])?$this->auth_context['scope']:null;
    }

    /**
     * @return null|string
     */
    public function getCurrentAccessToken()
    {
        return isset($this->auth_context['access_token'])?$this->auth_context['access_token']:null;
    }


    /**
     * @return null|string
     */
    public function getCurrentAccessTokenLifetime()
    {
        return isset($this->auth_context['expires_in'])?$this->auth_context['expires_in']:null;
    }

    /**
     * @return null
     */
    public function getCurrentClientId()
    {
        return isset($this->auth_context['client_id'])?$this->auth_context['client_id']:null;
    }

    /**
     * @param $auth_context
     */
    public function setAuthorizationContext($auth_context)
    {
        $this->auth_context = $auth_context;
    }

    /**
     * @return null
     */
    public function getCurrentUserId()
    {
        return isset($this->auth_context['user_id'])?$this->auth_context['user_id']:null;
    }
}