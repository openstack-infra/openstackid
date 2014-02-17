<?php

namespace oauth2\strategies;

use oauth2\requests\OAuth2Request;
use oauth2\responses\OAuth2IndirectErrorResponse;
use oauth2\responses\OAuth2IndirectFragmentErrorResponse;
use oauth2\responses\OAuth2IndirectResponse;
use oauth2\OAuth2Protocol;
use ReflectionClass;

class OAuth2IndirectErrorResponseFactoryMethod {

    /**
     * @param OAuth2Request $request
     * @param $error
     * @param $return_url
     * @return null|OAuth2IndirectErrorResponse|OAuth2IndirectFragmentErrorResponse
     * @throws Exception
     */
    public static function buildResponse(OAuth2Request $request = null,$error, $return_url){
        $response = null;
        $reflector = new ReflectionClass($request);
        $class_name = $reflector->getName();
        if($class_name =='oauth2\requests\OAuth2AuthorizationRequest'){
            $response_type = $request->getResponseType();
            switch($response_type){
                case OAuth2Protocol::OAuth2Protocol_ResponseType_Token:
                    return new OAuth2IndirectFragmentErrorResponse($error,$return_url);
                    break;
                case OAuth2Protocol::OAuth2Protocol_ResponseType_Code:
                    return new OAuth2IndirectErrorResponse($error,$return_url);
                    break;
                default:
                        throw new Exception(sprintf("invalid response type %s",$response_type));
                    break;
            }
        }
        return $response;
    }
} 