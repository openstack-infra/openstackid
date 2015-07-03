<?php

namespace oauth2\strategies;

use oauth2\requests\OAuth2Request;
use oauth2\responses\OAuth2IndirectErrorResponse;
use oauth2\responses\OAuth2IndirectFragmentErrorResponse;
use oauth2\OAuth2Protocol;
use Exception;
use oauth2\requests\OAuth2AuthorizationRequest;

/**
 * Class OAuth2IndirectErrorResponseFactoryMethod
 * @package oauth2\strategies
 */
final class OAuth2IndirectErrorResponseFactoryMethod
{

    /**
     * @param OAuth2Request $request
     * @param string $error
     * @param string $error_description
     * @param string|null $return_url
     * @return null|OAuth2IndirectErrorResponse|OAuth2IndirectFragmentErrorResponse
     * @throws Exception
     */
    public static function buildResponse(OAuth2Request $request = null, $error, $error_description, $return_url = null)
    {

        $response = null;

        if($request instanceof OAuth2AuthorizationRequest)
        {
            $response_type = $request->getResponseType();

            switch($response_type)
            {
                case OAuth2Protocol::OAuth2Protocol_ResponseType_Token:
                    return new OAuth2IndirectFragmentErrorResponse($error, $error_description,  $return_url, $request->getState());
                    break;
                case OAuth2Protocol::OAuth2Protocol_ResponseType_Code:
                    return new OAuth2IndirectErrorResponse($error, $error_description,$return_url, $request->getState());
                    break;
                default:
                    throw new Exception
                    (
                        sprintf
                        (
                            "invalid response type %s",
                            $response_type
                        )
                    );
                break;
            }
        }
        return $response;
    }
} 