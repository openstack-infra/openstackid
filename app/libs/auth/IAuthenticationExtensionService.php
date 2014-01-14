<?php

namespace auth;

/**
 * Interface IAuthenticationExtensionService
 * @package auth
 */
interface IAuthenticationExtensionService {
    /**
     * @return array
     */
    public function getExtensions();

    /**
     * @param IAuthenticationExtension $extension
     * @return bool
     */
    public function addExtension(IAuthenticationExtension $extension);
} 