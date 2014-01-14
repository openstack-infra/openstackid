<?php

namespace auth;

/**
 * Class AuthenticationExtensionService
 * @package auth
 */
class AuthenticationExtensionService implements IAuthenticationExtensionService {

    private $extensions;

    public function __construct(){
        $this->extensions = array();
    }
    /**
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param IAuthenticationExtension $extension
     * @return bool
     */
    public function addExtension(IAuthenticationExtension $extension)
    {
       array_push($this->extensions, $extension);
    }
}