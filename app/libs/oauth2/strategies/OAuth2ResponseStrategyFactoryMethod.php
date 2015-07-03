<?php

namespace oauth2\strategies;

use oauth2\requests\OAuth2AuthenticationRequest;
use oauth2\requests\OAuth2Request;
use oauth2\responses\OAuth2DirectResponse;
use oauth2\responses\OAuth2IndirectFragmentResponse;
use oauth2\responses\OAuth2IndirectResponse;
use oauth2\responses\OAuth2PostResponse;
use oauth2\responses\OAuth2Response;
use utils\IHttpResponseStrategy;
use utils\services\ServiceLocator;
use oauth2\OAuth2Protocol;

/**
 * Class OAuth2ResponseStrategyFactoryMethod
 * @package oauth2\strategies
 */
final class OAuth2ResponseStrategyFactoryMethod
{

    /**
     * @param OAuth2Request $request
     * @param OAuth2Response $response
     * @return IHttpResponseStrategy
     * @throws \Exception
     */
    public static function buildStrategy(OAuth2Request $request, OAuth2Response $response)
    {
        $type = $response->getType();

        if($request instanceof OAuth2AuthenticationRequest)
        {
            $response_mode = $request->getResponseMode();

            if(is_null($response_mode))
            {
                $response_mode = OAuth2Protocol::getDefaultResponseMode($request->getResponseType(false));
            }

            switch($response_mode)
            {
                case OAuth2Protocol::OAuth2Protocol_ResponseMode_Fragment:
                    $type = OAuth2IndirectFragmentResponse::OAuth2IndirectFragmentResponse;
                    break;
                case OAuth2Protocol::OAuth2Protocol_ResponseMode_Query:
                    $type = OAuth2IndirectResponse::OAuth2IndirectResponse;
                    break;
                case OAuth2Protocol::OAuth2Protocol_ResponseMode_FormPost:
                    $type = OAuth2PostResponse::OAuth2PostResponse;
                    break;
                case OAuth2Protocol::OAuth2Protocol_ResponseMode_Direct:
                    $type = OAuth2DirectResponse::OAuth2DirectResponse;
                    break;
            }
        }

        switch ($type)
        {
            case OAuth2PostResponse::OAuth2PostResponse:
            {
                return ServiceLocator::getInstance()->getService(OAuth2PostResponse::OAuth2PostResponse);
            }
            break;
            case OAuth2IndirectResponse::OAuth2IndirectResponse:
            {
                return ServiceLocator::getInstance()->getService(OAuth2IndirectResponse::OAuth2IndirectResponse);
            }
            break;

            case OAuth2IndirectFragmentResponse::OAuth2IndirectFragmentResponse:
            {
                return ServiceLocator::getInstance()->getService(OAuth2IndirectFragmentResponse::OAuth2IndirectFragmentResponse);
            }
            break;
            case OAuth2DirectResponse::OAuth2DirectResponse:
            {
                return ServiceLocator::getInstance()->getService(OAuth2DirectResponse::OAuth2DirectResponse);
            }
            break;
            default:
                throw new \Exception(sprintf("Invalid OAuth2 response Type %s", $type));
            break;
        }
    }
} 