<form id="form-application-security" name="form-application-security">
    <div class="form-group">
        <label for="default_max_age">Default Max. Age (optional)</label>
        <input type="text" name="default_max_age" class="form-control" id="default_max_age"
               value="{{$client->default_max_age}}">
    </div>
    <div class="form-group">
        <label for="token_endpoint_auth_method">Token Endpoint Authorization Method</label>
        {{ Form::select('token_endpoint_auth_method', utils\ArrayUtils::convert2Assoc( oauth2\OAuth2Protocol::$token_endpoint_auth_methods), $client->token_endpoint_auth_method, array('class' => 'form-control')) }}
    </div>
    <div class="form-group">
        <label for="subject_type">Token Endpoint Authorization Method</label>
        {{ Form::select('subject_type', utils\ArrayUtils::convert2Assoc(Client::$valid_subject_types), $client->subject_type, array('class' => 'form-control')) }}
    </div>
    <div class="form-group">
        <label for="jwks_uri">JWK Url</label>
        <input type="text" name="jwks_uri" id="jwks_uri" maxlength="255"
               value="{{$client->jwks_uri}}" class="form-control">
    </div>
    <div class="form-group row">
        <div class="col-md-6">
        <label for="userinfo_signed_response_alg">UserInfo Signed Response Algorithm</label>
        {{ Form::select('userinfo_signed_response_alg', utils\ArrayUtils::convert2Assoc(oauth2\OAuth2Protocol::$supported_signing_algorithms), $client->userinfo_signed_response_alg, array('class' => 'form-control')) }}
            </div>
            <div class="col-md-6">
        <label for=" id_token_signed_response_alg">Id Token Signed Response
            Algorithm</label>
        {{ Form::select(' id_token_signed_response_alg', utils\ArrayUtils::convert2Assoc(oauth2\OAuth2Protocol::$supported_signing_algorithms), $client-> id_token_signed_response_alg, array('class' => 'form-control')) }}
                </div>
    </div>
    <div class="form-group row">
        <div class="col-md-6">
        <label for="userinfo_encrypted_response_alg">UserInfo Encrypted Key
            Algorithm</label>
        {{ Form::select('userinfo_encrypted_response_alg', utils\ArrayUtils::convert2Assoc( oauth2\OAuth2Protocol::$supported_key_management_algorithms), $client->userinfo_encrypted_response_alg, array('class' => 'form-control')) }}
        </div>
        <div class="col-md-6">
        <label for="id_token_encrypted_response_alg">Id Token Encrypted Key
            Algorithm</label>
      {{ Form::select('id_token_encrypted_response_alg', utils\ArrayUtils::convert2Assoc( oauth2\OAuth2Protocol::$supported_key_management_algorithms), $client->id_token_encrypted_response_alg, array('class' => 'form-control')) }}
        </div>
    </div>
    <div class="form-group row">
        <div class="col-md-6">
        <label for="userinfo_encrypted_response_enc">UserInfo Encrypted Content
            Algorithm</label>
        {{ Form::select('userinfo_encrypted_response_enc', utils\ArrayUtils::convert2Assoc(oauth2\OAuth2Protocol::$supported_content_encryption_algorithms), $client->userinfo_encrypted_response_enc, array('class' => 'form-control')) }}
        </div>
        <div class="col-md-6">
        <label for="id_token_encrypted_response_enc">Id Token Encrypted Content
            Algorithm</label>
        {{ Form::select('id_token_encrypted_response_enc', utils\ArrayUtils::convert2Assoc(oauth2\OAuth2Protocol::$supported_content_encryption_algorithms), $client->id_token_encrypted_response_enc, array('class' => 'form-control')) }}
        </div>
    </div>
    <button type="submit" class="btn btn-default btn-md active">Save</button>
</form>