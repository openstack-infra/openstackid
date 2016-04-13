<?php namespace Auth;

/**
 * Interface IAuthenticationExtensionService
 * @package Auth
 */
interface IAuthenticationExtensionService {
    /**
     * @return array
     */
    public function getExtensions();

    /**
     * @param IAuthenticationExtension $extension
     * @return $this
     */
    public function addExtension(IAuthenticationExtension $extension);
} 