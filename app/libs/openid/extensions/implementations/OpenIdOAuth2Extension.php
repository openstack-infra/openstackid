<?php

namespace openid\extensions\implementations;

use openid\extensions\OpenIdExtension;
use openid\OpenIdProtocol;
use openid\requests\contexts\RequestContext;
use openid\requests\OpenIdRequest;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdResponse;
use Exception;
use utils\services\Registry;
use oauth2\requests\OAuth2AuthorizationRequest;
use oauth2\OAuth2Protocol;
use oauth2\OAuth2Message;

/**
 * Class OpenIdOAuthExtension
 * Implements
 * http://step2.googlecode.com/svn/spec/openid_oauth_extension/latest/openid_oauth_extension.html
 * @package openid\extensions\implementations
 */
class OpenIdOAuth2Extension extends OpenIdExtension
{

    const Prefix = "oauth";
    const NamespaceUrl = "http://specs.openid.net/extensions/oauth/2.0";
    const NamespaceType = 'ns';

    private $oauth2_protocol;

    public function __construct($name, $namespace, $view, $description)
    {
        parent::__construct($name, $namespace, $view, $description);
        $this->oauth2_protocol =  Registry::getInstance()->get('oauth2\IOAuth2Protocol');
    }

    public static function param($param, $separator = '.')
    {
        return OpenIdProtocol::OpenIdPrefix . $separator . self::Prefix . $separator . $param;
    }

    public static function paramNamespace($separator = '.')
    {
        return OpenIdProtocol::OpenIdPrefix . $separator . OpenIdProtocol::OpenIDProtocol_NS . $separator . self::Prefix;
    }

    public function parseRequest(OpenIdRequest $request, RequestContext $context)
    {
        try {
            $oauth2_request = new OpenIdOAuth2Request($request->getMessage());
            if (!$oauth2_request->isValid()) return;
            $scopes = $oauth2_request->getScope();
            $partial_view = new PartialView($this->view, array("scopes" => explode(' ', $scopes)));
            $context->addPartialView($partial_view);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
        }
    }

    public function prepareResponse(OpenIdRequest $request, OpenIdResponse $response, ResponseContext $context)
    {
        try{

            $oauth2_request = new OpenIdOAuth2Request($request->getMessage());
            if (!$oauth2_request->isValid()) return;

            //get auth code
            $oauth2_msg = new OAuth2Message(
                array(
                    OAuth2Protocol::OAuth2Protocol_ClientId     => $oauth2_request->getClientId(),
                    OAuth2Protocol::OAuth2Protocol_Scope        => $oauth2_request->getScope(),
                    OAuth2Protocol::OAuth2Protocol_State        => $oauth2_request->getState(),
                    OAuth2Protocol::OAuth2Protocol_RedirectUri  => $request->getParam(OpenIdProtocol::OpenIDProtocol_ReturnTo),
                    OAuth2Protocol::OAuth2Protocol_ResponseType => OAuth2Protocol::OAuth2Protocol_ResponseType_Code
                )
            );
            // do oauth2 Authorization Code Grant 1st step (get auth code to exchange for an access token)
            // http://tools.ietf.org/html/rfc6749#section-4.1
            $oauth2_response = $this->oauth2_protocol->authorize(new OAuth2AuthorizationRequest($oauth2_msg));
            if ( get_class($oauth2_response) =='oauth2\\responses\\OAuth2AuthorizationResponse') {
                //add namespace
                $response->addParam(self::paramNamespace(),self::NamespaceUrl );
                $context->addSignParam(self::paramNamespace());

                //add auth code
                $response->addParam(self::param(OAuth2Protocol::OAuth2Protocol_ResponseType_Code ), $oauth2_response->getAuthCode());
                $context->addSignParam(self::param(OAuth2Protocol::OAuth2Protocol_ResponseType_Code ));

                //add requested scope
                $response->addParam(self::param(OAuth2Protocol::OAuth2Protocol_Scope ), $oauth2_response->getScope());
                $context->addSignParam(self::param(OAuth2Protocol::OAuth2Protocol_Scope ));

                //add state (if present)
                $state = $oauth2_response->getState();
                if(!is_null($state)){
                    $response->addParam(self::param(OAuth2Protocol::OAuth2Protocol_State), $state);
                    $context->addSignParam(self::param(OAuth2Protocol::OAuth2Protocol_State));
                }
            }
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
        }
    }

    public function getTrustedData(OpenIdRequest $request)
    {

    }

}