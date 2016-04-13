<form id="form-application-security" name="form-application-security">
    <div class="form-group">
        <label for="default_max_age">Default Max. Age (optional)&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
        aria-hidden="true"
        title="Default Maximum Authentication Age. Specifies that the End-User MUST be actively authenticated if the End-User was authenticated longer ago than the specified number of seconds. The max_age request parameter overrides this default value. If omitted, no default Maximum Authentication Age is specified."></span></label>
        <input type="text" name="default_max_age" class="form-control" id="default_max_age"
               value="{!!$client->default_max_age!!}">
    </div>
    @if(OAuth2\OAuth2Protocol::isClientAllowedToUseTokenEndpointAuth($client))
    <div class="form-group">
        <label for="token_endpoint_auth_method">Token Endpoint Authorization Method&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
                                                                                          aria-hidden="true"
            title="Requested Client Authentication method for the Token Endpoint. The options are client_secret_post, client_secret_basic, client_secret_jwt, private_key_jwt, and none, as described in Section 9 of OpenID Connect Core 1.0 [OpenID.Core]. Other authentication methods MAY be defined by extensions. If omitted, the default is client_secret_basic -- the HTTP Basic Authentication Scheme specified in Section 2.3.1 of OAuth 2.0 [RFC6749]."></span></label>
        {!! Form::select('token_endpoint_auth_method', OAuth2\OAuth2Protocol::getTokenEndpointAuthMethodsPerClientType($client), $client->token_endpoint_auth_method, array('class' => 'form-control', 'id' => 'token_endpoint_auth_method')) !!}
    </div>
     <div class="form-group" id="token_endpoint_auth_signing_alg_group" >
        <label for="token_endpoint_auth_signing_alg">Token Endpoint Authorization Signed Algorithm&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
        aria-hidden="true"
        title=" JWS [JWS] alg algorithm [JWA] that MUST be used for signing the JWT [JWT] used to authenticate the Client at the Token Endpoint for the private_key_jwt and client_secret_jwt authentication methods. All Token Requests using these authentication methods from this Client MUST be rejected, if the JWT is not signed with this algorithm. Servers SHOULD support RS256. The value none MUST NOT be used. The default, if omitted, is that any algorithm supported by the OP and the RP MAY be used."></span></label>
        {!! Form::select('token_endpoint_auth_signing_alg', OAuth2\OAuth2Protocol::getSigningAlgorithmsPerClientType($client), $client->token_endpoint_auth_signing_alg, array('class' => 'form-control', 'id' => 'token_endpoint_auth_signing_alg')) !!}
     </div>
    @endif
    <div class="form-group">
        <label for="subject_type">Subject Type&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
        aria-hidden="true"
        title="subject_type requested for responses to this Client. The subject_types_supported Discovery parameter contains a list of the supported subject_type values for this server. Valid types include pairwise and public. "></span></label>
        {!! Form::select('subject_type', Utils\ArrayUtils::convert2Assoc(\Models\OAuth2\Client::$valid_subject_types), $client->subject_type, array('class' => 'form-control', 'id' => 'subject_type')) !!}
    </div>
    <div class="form-group">
        <label for="jwks_uri">JWK Url&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
        aria-hidden="true"
        title="URL for the Client's JSON Web Key Set [JWK] document. If the Client signs requests to the Server, it contains the signing key(s) the Server uses to validate signatures from the Client. The JWK Set MAY also contain the Client's encryption keys(s), which are used by the Server to encrypt responses to the Client. When both signing and encryption keys are made available, a use (Key Use) parameter value is REQUIRED for all keys in the referenced JWK Set to indicate each key's intended usage. Although some algorithms allow the same key to be used for both signatures and encryption, doing so is NOT RECOMMENDED, as it is less secure. The JWK x5c parameter MAY be used to provide X.509 representations of keys provided. When used, the bare key values MUST still be present and MUST match those in the certificate. "></span></label>
        <input type="text" name="jwks_uri" id="jwks_uri" maxlength="255"
               value="{!!$client->jwks_uri!!}" class="form-control">
    </div>
    <div class="form-group row">
        <div class="col-md-6 form-group ">
            <label for="userinfo_signed_response_alg">UserInfo Signed Response Algorithm&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
            aria-hidden="true"
            title="JWS alg algorithm [JWA] REQUIRED for signing UserInfo Responses. If this is specified, the response will be JWT [JWT] serialized, and signed using JWS. The default, if omitted, is for the UserInfo Response to return the Claims as a UTF-8 encoded JSON object using the application/json content-type."></span></label>
            {!! Form::select('userinfo_signed_response_alg', OAuth2\OAuth2Protocol::getSigningAlgorithmsPerClientType($client), $client->userinfo_signed_response_alg, array('class' => 'form-control', 'id' => 'userinfo_signed_response_alg')) !!}
        </div>
        <div class="col-md-6 form-group ">
            <label for="id_token_signed_response_alg">Id Token Signed Response Algorithm&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
            aria-hidden="true"
            title="JWS alg algorithm [JWA] REQUIRED for signing the ID Token issued to this Client. The value none MUST NOT be used as the ID Token alg value unless the Client uses only Response Types that return no ID Token from the Authorization Endpoint (such as when only using the Authorization Code Flow). The default, if omitted, is RS256. The public key for validating the signature is provided by retrieving the JWK Set referenced by the jwks_uri element from OpenID Connect Discovery 1.0 [OpenID.Discovery]."></span></label>
            {!! Form::select('id_token_signed_response_alg', OAuth2\OAuth2Protocol::getSigningAlgorithmsPerClientType($client), $client->id_token_signed_response_alg, array('class' => 'form-control', 'id' => 'id_token_signed_response_alg')) !!}
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-6 form-group ">
            <label for="userinfo_encrypted_response_alg">UserInfo Encrypted Key Algorithm&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
                                                                                                     aria-hidden="true"></span></label>
            {!! Form::select('userinfo_encrypted_response_alg', OAuth2\OAuth2Protocol::getKeyManagementAlgorithmsPerClientType($client), $client->userinfo_encrypted_response_alg, array('class' => 'form-control', 'id' => 'userinfo_encrypted_response_alg')) !!}
        </div>
        <div class="col-md-6 form-group ">
            <label for="id_token_encrypted_response_alg">Id Token Encrypted Key Algorithm&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
                                                                                                     aria-hidden="true"></span></label>
            {!! Form::select('id_token_encrypted_response_alg', OAuth2\OAuth2Protocol::getKeyManagementAlgorithmsPerClientType($client), $client->id_token_encrypted_response_alg, array('class' => 'form-control', 'id' => 'id_token_encrypted_response_alg')) !!}
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-6 form-group ">
            <label for="userinfo_encrypted_response_enc">UserInfo Encrypted Content Algorithm&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
                                                                                                         aria-hidden="true"></span></label>
            {!! Form::select('userinfo_encrypted_response_enc', Utils\ArrayUtils::convert2Assoc(OAuth2\OAuth2Protocol::$supported_content_encryption_algorithms), $client->userinfo_encrypted_response_enc, array('class' => 'form-control', 'id' => 'userinfo_encrypted_response_enc')) !!}
        </div>
        <div class="col-md-6 form-group ">
            <label for="id_token_encrypted_response_enc">Id Token Encrypted Content Algorithm&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle"
                                                                                                         aria-hidden="true"></span></label>
            {!! Form::select('id_token_encrypted_response_enc', Utils\ArrayUtils::convert2Assoc(OAuth2\OAuth2Protocol::$supported_content_encryption_algorithms), $client->id_token_encrypted_response_enc, array('class' => 'form-control', 'id' => 'id_token_encrypted_response_enc')) !!}
        </div>
    </div>
    <input type="hidden" id="id" name="id" value="{!!$client->id!!}"/>
    <input type="hidden" id="application_type" name="application_type" value="{!!$client->application_type!!}"/>
    <input type="hidden" id="user_id"   name="user_id" value="{!!$client->user_id!!}"/>
    <button type="submit" class="btn btn-default btn-md active" id="save-application-security">Save</button>
</form>
@section('scripts')
    {!! HTML::script('assets/js/oauth2/profile/edit-client-security-main-settings.js') !!}
@append