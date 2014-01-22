<?php

namespace oauth2\services;


class OAuth2ServiceCatalog {
    const MementoService        = 'oauth2\\services\\IMementoOAuth2AuthenticationRequestService';
    const TokenService          = 'oauth2\\services\\ITokenService';
    const ClientService         = 'oauth2\\services\\IClientService';
    const ScopeService          = 'oauth2\\services\\IApiScopeService';
    const ResourceServerService = 'oauth2\\services\\IResourceServerService';
    const ApiService            = 'oauth2\\services\\IApiService';
    const ApiEndpointService    = 'oauth2\\services\\IApiEndpointService';
}