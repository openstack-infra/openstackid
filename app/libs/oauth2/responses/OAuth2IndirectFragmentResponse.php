<?php

namespace oauth2\responses;

class OAuth2IndirectFragmentResponse extends OAuth2IndirectResponse
{

    const OAuth2IndirectFragmentResponse ='OAuth2IndirectFragmentResponse';

    public function __construct()
    {
        parent::__construct();
    }

    public function getType()
    {
        return self::OAuth2IndirectFragmentResponse;
    }
} 