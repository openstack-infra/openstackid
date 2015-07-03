<?php

namespace oauth2\services;

/**
 * Class OAuth2ServiceCatalog
 * @package oauth2\services
 */
abstract class OAuth2ServiceCatalog
{

    const TokenService                       = 'oauth2\\services\\ITokenService';
    const ClientService                      = 'oauth2\\services\\IClientService';
    const ScopeService                       = 'oauth2\\services\\IApiScopeService';
    const ResourceServerService              = 'oauth2\\services\\IResourceServerService';
    const ApiService                         = 'oauth2\\services\\IApiService';
    const ApiEndpointService                 = 'oauth2\\services\\IApiEndpointService';
    const UserConsentService                 = 'oauth2\\services\\IUserConsentService';
    const AllowedOriginService               = 'oauth2\\services\\IAllowedOriginService';
    const ClienPublicKeyService              = 'oauth2\\services\\IClienPublicKeyService';
    const ServerPrivateKeyService            = 'oauth2\\services\\IServerPrivateKeyService';
    const OpenIDProviderConfigurationService = 'oauth2\\discovery\\IOpenIDProviderConfigurationService';
    const MementoSerializerService           = "oauth2\\services\\IMementoOAuth2SerializerService";
}