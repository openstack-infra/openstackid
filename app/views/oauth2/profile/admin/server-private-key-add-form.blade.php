<form id="form-add-private-key" name="form-add-private-key">
    <div class="form-group">
        <label class="control-label" for="kid">Key Identifier&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="The 'kid' (key ID) parameter is used to match a specific key. This is used, for instance, to choose among a set of keys within a JWK Set during key rollover. The structure of the 'kid' value is unspecified.  When 'kid' values are used within a JWK Set, different keys within the JWK Set SHOULD use distinct 'kid' values.  (One example in which different keys might use the same 'kid' value is if they have different 'kty' (key type) values but are considered to be equivalent alternatives by the application using them.)"></span></label>
        <input type="text" class="form-control" name="kid" id="kid" autocomplete="off">
    </div>
    <div class="form-group">
        <label class="control-label" for="datepicker">Key validity range&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
        <div class="input-daterange input-group" id="datepicker">
            <input type="text" class="input-sm form-control" name="valid_from" id=="valid_from" autocomplete="off" />
            <span class="input-group-addon">to</span>
            <input type="text" class="input-sm form-control" name="valid_to" id="valid_to" autocomplete="off" />
        </div>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="active" name="active" checked>Is Active?&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span>
        </label>
    </div>
    <div class="form-group">
        <label class="control-label" for="usage">Usage&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="The 'use' parameter identifies the intended use of the key."></span></label>
        {{ Form::select('usage', utils\ArrayUtils::convert2Assoc(\jwk\JSONWebKeyPublicKeyUseValues::$valid_uses) ,null , array('class' => 'form-control', 'id' => 'usage')) }}
    </div>
    <div class="form-group">
        <label class="control-label" for="alg">Algorithm&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="identifies the algorithm intended for
   use with the key"></span></label>
        {{ Form::select('alg', array() ,null , array('class' => 'form-control', 'id' => 'alg')) }}
    </div>
    <div class="form-group">
        <label class="control-label" for="password">Key Password&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
        <input type="password" class="form-control" name="password" id="password" maxlength="255" autocomplete="off"/>
    </div>
    <div class="form-group">
        <label class="control-label" for="password-confirmation">Key Confirmation Password&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
        <input type="password" class="form-control" name="password-confirmation" id="password-confirmation" maxlength="255" autocomplete="off"/>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="autogenerate" name="autogenerate" checked>Should Autogenerate?&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="If checkeck, private key value will be autogenerated with a key length of 2048 bits."></span>
        </label>
    </div>
    <div class="form-group" id="pem_container" style="display:none">
        <label class="control-label" for="pem_content">Key&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
        <textarea class="form-control" rows="10" cols="40" name="pem_content" id="pem_content" autocomplete="off"></textarea>
    </div>
</form>