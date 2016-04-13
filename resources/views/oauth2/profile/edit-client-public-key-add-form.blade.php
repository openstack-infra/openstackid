<form id="form-add-public-key" name="form-add-public-key">
    <div class="form-group">
        <label class="control-label" for="kid">Key Identifier&nbsp;<span class="glyphicon glyphicon-info-sign pointable" aria-hidden="true" title=""></span></label>
        <input type="text" class="form-control" name="kid" id="kid" autocomplete="off">
    </div>
    <div class="form-group">
        <label class="control-label" for="datepicker">Key validity range&nbsp;<span class="glyphicon glyphicon-info-sign pointable" aria-hidden="true" title=""></span></label>
        <div class="input-daterange input-group" id="datepicker">
            <input type="text" class="input-sm form-control" name="valid_from"  autocomplete="off"/>
            <span class="input-group-addon">to</span>
            <input type="text" class="input-sm form-control" name="valid_to"  autocomplete="off"/>
        </div>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="active" name="active" checked>Is Active?
        </label>
    </div>
    <div class="form-group">
        <label class="control-label" for="usage">Usage&nbsp;<span class="glyphicon glyphicon-info-sign pointable" aria-hidden="true" title=""></span></label>
        {{ Form::select('usage', Utils\ArrayUtils::convert2Assoc(\jwk\JSONWebKeyPublicKeyUseValues::$valid_uses) ,null , array('class' => 'form-control', 'id' => 'usage')) }}
    </div>
    <div class="form-group">
        <label class="control-label" for="alg">Algorithm&nbsp;<span class="glyphicon glyphicon-info-sign pointable" aria-hidden="true" title="identifies the algorithm intended for
   use with the key"></span></label>
        {{ Form::select('alg', array() ,null , array('class' => 'form-control', 'id' => 'alg')) }}
    </div>
    <div class="form-group">
        <label class="control-label" for="pem_content">Key&nbsp;<span class="glyphicon glyphicon-info-sign pointable" aria-hidden="true" title=""></span></label>
        <textarea class="form-control" rows="10" cols="40" name="pem_content" id="pem_content" autocomplete="off"></textarea>
    </div>
</form>