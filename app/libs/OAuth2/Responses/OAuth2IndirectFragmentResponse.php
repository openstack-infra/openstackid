<?php namespace OAuth2\Responses;
/**
 * Class OAuth2IndirectFragmentResponse
 * @package OAuth2\Responses
 */
class OAuth2IndirectFragmentResponse extends OAuth2IndirectResponse
{

    const OAuth2IndirectFragmentResponse ='OAuth2IndirectFragmentResponse';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::OAuth2IndirectFragmentResponse;
    }
} 