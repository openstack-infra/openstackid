<?php

namespace oauth2;

/**
 * Interface IResourceServerContext
 * Current Request OAUTH2 security context
 * @package oauth2
 */
interface IResourceServerContext {

    /**
     * returns given scopes for current requewt
     * @return array
     */
    public function getCurrentScope();

    /**
     * gets current access token valaue
     * @return string
     */
    public function getCurrentAccessToken();

    /**
     * gets current access token lifetime
     * @return mixed
     */
    public function getCurrentAccessTokenLifetime();

    /**
     * gets current client id
     * @return string
     */
    public function getCurrentClientId();

    /**
     * gets current user id (if was set)
     * @return int
     */
    public function getCurrentUserId();

    public function setAuthorizationContext($auth_context);
} 