<?php namespace OpenId\Extensions\Implementations;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use OAuth2\IOAuth2Protocol;
use OAuth2\Services\IApiScopeService;
use OAuth2\Services\IClientService;
use OpenId\Requests\Contexts\PartialView;
use OpenId\Extensions\OpenIdExtension;
use OpenId\OpenIdProtocol;
use OpenId\Requests\contexts\RequestContext;
use OpenId\Requests\OpenIdRequest;
use OpenId\Responses\contexts\ResponseContext;
use OpenId\Responses\OpenIdResponse;
use Exception;
use Utils\Services\ICheckPointService;
use Utils\Services\ILogService;
use OAuth2\Requests\OAuth2AuthorizationRequest;
use OAuth2\OAuth2Protocol;
use OAuth2\OAuth2Message;
/**
 * Class OpenIdOAuthExtension
 * Implements
 * http://step2.googlecode.com/svn/spec/openid_oauth_extension/latest/openid_oauth_extension.html
 * OpenID+OAuth Hybrid protocol lets web developers combine an OpenID request with an
 * OAuth authentication request. This extension is useful for web developers who use both OpenID
 * and OAuth, particularly in that it simplifies the process for users by requesting
 * their approval once instead of twice.
 * In this way, the user can approve login and service access at the same time.
 * @package OpenId\Extensions\Implementations
 */
class OpenIdOAuth2Extension extends OpenIdExtension
{

    const Prefix        = "oauth";
    const NamespaceUrl  = "http://specs.openid.net/extensions/oauth/2.0";
    const NamespaceType = 'ns';
    const RequestToken  = 'request_token';
    const Scope         = OAuth2Protocol::OAuth2Protocol_Scope;
    const ClientId      = OAuth2Protocol::OAuth2Protocol_ClientId;
    const State         = OAuth2Protocol::OAuth2Protocol_State;

    /**
     * @var IOAuth2Protocol
     */
    private $oauth2_protocol;
    /**
     * @var ICheckPointService
     */
    private $checkpoint_service;
    /**
     * @var IClientService
     */
    private $client_service;
    /**
     * @var IApiScopeService
     */
    private $scope_service;

	/**
	 * @param string             $name
	 * @param string             $namespace
	 * @param string             $view_name
	 * @param string             $description
	 * @param IOAuth2Protocol    $oauth2_protocol
	 * @param IClientService     $client_service
	 * @param IApiScopeService   $scope_service
	 * @param ICheckPointService $checkpoint_service
	 * @param ILogService        $log_service
	 */
	public function __construct($name, $namespace, $view_name, $description,
	                            IOAuth2Protocol $oauth2_protocol,
	                            IClientService $client_service,
	                            IApiScopeService $scope_service,
	                            ICheckPointService $checkpoint_service,
	                            ILogService $log_service)
    {
        parent::__construct($name, $namespace, $view_name, $description,$log_service);

        $this->oauth2_protocol     = $oauth2_protocol;
	    $this->client_service      = $client_service;
	    $this->scope_service       = $scope_service;
        $this->checkpoint_service  = $checkpoint_service;
    }

    /**
     * @param $param
     * @param string $separator
     * @return string
     */
    public static function param($param, $separator = '.')
    {
        return OpenIdProtocol::OpenIdPrefix . $separator . self::Prefix . $separator . $param;
    }

    /**
     * @param string $separator
     * @return string
     */
    public static function paramNamespace($separator = '.')
    {
        return OpenIdProtocol::OpenIdPrefix . $separator . OpenIdProtocol::OpenIDProtocol_NS . $separator . self::Prefix;
    }

    /**
     * @param OpenIdRequest $request
     * @param RequestContext $context
     * @return mixed|void
     */
    public function parseRequest(OpenIdRequest $request, RequestContext $context)
    {
        try {

            $oauth2_request = new OpenIdOAuth2Request($request->getMessage());

            if (!$oauth2_request->isValid()){
                return;
            }

            $scopes    = $oauth2_request->getScope();
            $client_id = $oauth2_request->getClientId();

            $client = $this->client_service->getClientById($client_id);
            // do some validations to allow show the oauth2 sub view...
            if(is_null($client)){
                $this->log_service->warning_msg(sprintf("OpenIdOAuth2Extension: client id %s not found!.",$client_id));
                return;
            }

            //check is redirect uri is allowed for client
            $redirect_uri = $request->getParam(OpenIdProtocol::OpenIDProtocol_ReturnTo);
            if (!$client->isUriAllowed($redirect_uri)){
                $this->log_service->warning_msg(sprintf("OpenIdOAuth2Extension: url %s not allowed for client id %s ",$redirect_uri,$client_id));
                return;
            }

            //check if requested client is allowed to use this scopes
            if(!$client->isScopeAllowed($scopes)){
                $this->log_service->warning_msg(sprintf("OpenIdOAuth2Extension: scope %s not allowed for client id %s ",$scopes,$client_id));
                return;
            }

            $scopes           = explode(' ', $scopes);
            //get scopes entities
            $requested_scopes = $this->scope_service->getScopesByName($scopes);

            // set view data

            $return_to = $request->getParam(OpenIdProtocol::OpenIDProtocol_ReturnTo);
            $url_parts = @parse_url($return_to);
            $return_to = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'];

            $partial_view = new PartialView ($this->view, array(
                'requested_scopes' => $requested_scopes,
                'app_name'         => $client->getApplicationName(),
                'app_logo'         => $client->getApplicationLogo(),
                'redirect_to'      => $return_to,
                'website'          => $client->getWebsite(),
                'dev_info_email'   => $client->getDeveloperEmail()
            ));

            $context->addPartialView($partial_view);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
        }
    }

    /**
     * @param OpenIdRequest $request
     * @param OpenIdResponse $response
     * @param ResponseContext $context
     * @return mixed|void
     */
    public function prepareResponse(OpenIdRequest $request, OpenIdResponse $response, ResponseContext $context)
    {
        try{
            $oauth2_request = new OpenIdOAuth2Request($request->getMessage());
            if (!$oauth2_request->isValid()) return;
            //get auth code
            $oauth2_msg = new OAuth2Message(
                array(
                    OAuth2Protocol::OAuth2Protocol_ClientId        => $oauth2_request->getClientId(),
                    OAuth2Protocol::OAuth2Protocol_Scope           => $oauth2_request->getScope(),
                    OAuth2Protocol::OAuth2Protocol_RedirectUri     => $request->getParam(OpenIdProtocol::OpenIDProtocol_ReturnTo),
                    OAuth2Protocol::OAuth2Protocol_State           => $oauth2_request->getState(),
                    OAuth2Protocol::OAuth2Protocol_Approval_Prompt => $oauth2_request->getApprovalPrompt(),
                    OAuth2Protocol::OAuth2Protocol_AccessType      => $oauth2_request->getAccessType(),
                    OAuth2Protocol::OAuth2Protocol_ResponseType    => OAuth2Protocol::OAuth2Protocol_ResponseType_Code
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

                //add state
                $response->addParam(self::param(self::State), $oauth2_request->getState());
                $context->addSignParam(self::param(self::State));

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

    /**
     * @param OpenIdRequest $request
     * @return array|mixed
     */
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