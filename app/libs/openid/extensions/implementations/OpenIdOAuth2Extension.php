<?php

namespace openid\extensions\implementations;

use openid\requests\contexts\PartialView;
use oauth2\services\OAuth2ServiceCatalog;
use openid\extensions\OpenIdExtension;
use openid\OpenIdProtocol;
use openid\requests\contexts\RequestContext;
use openid\requests\OpenIdRequest;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdResponse;
use Exception;

use utils\services\Registry;
use utils\services\UtilsServiceCatalog;

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

    const Prefix        = "oauth";
    const NamespaceUrl  = "http://specs.openid.net/extensions/oauth/2.0";
    const NamespaceType = 'ns';
    const RequestToken  = 'request_token';
    const Scope         = OAuth2Protocol::OAuth2Protocol_Scope;
    const ClientId      = OAuth2Protocol::OAuth2Protocol_ClientId;

    private $oauth2_protocol;
    private $checkpoint_service;
    private $client_service;
    private $scope_service;

    public function __construct($name, $namespace, $view, $description)
    {
        parent::__construct($name, $namespace, $view, $description);

        $this->oauth2_protocol     = Registry::getInstance()->get('oauth2\IOAuth2Protocol');
        $this->checkpoint_service  = Registry::getInstance()->get(UtilsServiceCatalog::CheckPointService);
        $this->client_service      = Registry::getInstance()->get(OAuth2ServiceCatalog::ClientService);
        $this->scope_service       = Registry::getInstance()->get(OAuth2ServiceCatalog::ScopeService);
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

            $scopes    = $oauth2_request->getScope();
            $client_id = $oauth2_request->getClientId();

            $client = $this->client_service->getClientById($client_id);
            // do some validations to allow show the oauth2 sub view...
            if(is_null($client)) return;

            //check if requested client is allowed to use this scopes
            if(!$client->isScopeAllowed($scopes)) return;

            $scopes           = explode(' ', $scopes);
            //get scopes entities
            $requested_scopes = $this->scope_service->getScopesByName($scopes);

            // set view data

            $partial_view = new PartialView ($this->view, array(
                'requested_scopes' => $requested_scopes,
                'app_name'         => $client->getApplicationName(),
                'app_logo'         => $client->getApplicationLogo(),
                'redirect_to'      => '#',
                'dev_info_email'   => $client->getDeveloperEmail()
            ));

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
                    OAuth2Protocol::OAuth2Protocol_RedirectUri  => $request->getParam(OpenIdProtocol::OpenIDProtocol_ReturnTo),
                    OAuth2Protocol::OAuth2Protocol_State        => $request->getParam(OpenIdProtocol::OpenIDProtocol_Nonce),
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
                $response->addParam(self::param(self::RequestToken), $oauth2_response->getAuthCode());
                $context->addSignParam(self::param(self::RequestToken));

                //add requested scope
                $response->addParam(self::param(self::Scope), $oauth2_response->getScope());
                $context->addSignParam(self::param(self::Scope));
            }
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            $this->checkpoint_service->trackException($ex);
            //http://step2.googlecode.com/svn/spec/openid_oauth_extension/latest/openid_oauth_extension.html#AuthResp
            /*
             * To note that the OAuth Authorization was declined or not valid, the Combined Provider SHALL only
             * respond with the parameter "openid.ns.oauth".
             */
            //add namespace
            $response->addParam(self::paramNamespace(),self::NamespaceUrl );
            $context->addSignParam(self::paramNamespace());
        }
    }

    public function getTrustedData(OpenIdRequest $request)
    {
        $data = array();
        try {
            $oauth2_request = new OpenIdOAuth2Request($request->getMessage());
            if ($oauth2_request->isValid()) {
                array_push($data,$oauth2_request->getScope());
                array_push($data,$oauth2_request->getClientId());
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
        }
        return $data;
    }

}