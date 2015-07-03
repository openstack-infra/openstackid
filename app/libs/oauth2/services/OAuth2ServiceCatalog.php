<?php

namespace oauth2\services;

/**
 * Class OAuth2ServiceCatalog
 * @package oauth2\services
 */
abstract class OAuth2ServiceCatalog
{
    const TokenService                       = 'oauth2\\services\\ITokenService';
    const ClientCredentialGenerator          = 'oauth2\\services\\IClientCrendentialGenerator';
    const ClientService                      = 'oauth2\\services\\IClientService';
    const ScopeService                       = 'oauth2\\services\\IApiScopeService';
    const ResourceServerService              = 'oauth2\\services\\IResourceServerService';
    const ApiService                         = 'oauth2\\services\\IApiService';
    const ApiEndpointService                 = 'oauth2\\services\\IApiEndpointService';
    const UserConsentService                 = 'oauth2\\services\\IUserConsentService';
    const ClienPublicKeyService              = 'oauth2\\services\\IClienPublicKeyService';
    const ServerPrivateKeyService            = 'oauth2\\services\\IServerPrivateKeyService';
    const OpenIDProviderConfigurationService = 'oauth2\\discovery\\IOpenIDProviderConfigurationService';
    const MementoSerializerService           = 'oauth2\\services\\IMementoOAuth2SerializerService';
    const AuthenticationStrategy             = 'oauth2\\strategies\\IOAuth2AuthenticationStrategy';
    const PrincipalService                    = 'oauth2\\services\\IPrincipalService';
    const SecurityContextService              = 'oauth2\\services\\ISecurityContextService';
}