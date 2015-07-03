@section('css')
    {{ HTML::style('assets/css/edit-client-public-keys.css') }}
@append
<table id="public-keys-table" class="table">
    <caption>
    </caption>
    <thead>
        <tr style="background-color: #f5f5f5;">
            <td width="90%" colspan="4">
                <h5 style="font-weight: bold">Public keys&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></h5>
            </td>
            <td width="10%">
                <a href="#" class="btn btn-default btn-sm active add-public-key">Add Public Key</a>
            </td>
        </tr>
        <tr>
            <td colspan="5">
            <p>This is a list of Public Keys keys associated with your application. Remove any keys that you do not recognize.</p>
            </td>
        </tr>
    </thead>
    <tbody id="body-public-keys">
    @foreach ($client->getPublicKeys() as $public_key)
    <tr id="tr_{{$public_key->id}}">
        <td width="7%">
            <div class="row">
                <div class="col-md-6">
                    <span data-public-key-id="{{$public_key->id}}" class="badge public-key-status {{ $public_key->active ? 'public-key-active':'public-key-deactivated' }}" title="{{ $public_key->active ? 'active':'deactivated' }}">&nbsp;</span>
                </div>
                <div class="col-md-6 col-md-offset-neg-1">
                    <i class="fa fa-key fa-2x pointable" title="{{$public_key->kid}}&nbsp;({{$public_key->type}})"></i>
                </div>
            </div>
        </td>
        <td colspan="3">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <strong class="public-key-title">{{$public_key->kid}}&nbsp;<span class="badge public-key-usage pointable" title="Key Usage">{{$public_key->usage}}</span>&nbsp;<span class="label label-info pointable" title="Key Type">{{$public_key->type}}</span></strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <code class="public-key-fingerprint">{{$public_key->getSHA_256_Thumbprint()}}</code>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <span class="public-key-validity-range">valid from <strong>{{$public_key->valid_from}}</strong> to <strong>{{$public_key->valid_to}}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </td>
        <td><a class="btn btn-default btn-sm active delete-public-key" href="#" data-public-key-id="{{$public_key->id}}">Delete</a></td>
    </tr>
    @endforeach
    </tbody>
</table>

<div class="alert alert-danger public-keys-empty-message" role="alert"
     @if(count($client->getPublicKeys()) > 0 )
     style="display: none"
     @endif
     >
    <p>There are no Public keys yet.</p>
</div>
<!-- Modal -->
<div class="modal fade" id="ModalAddPublicKey" tabindex="-1" role="dialog" aria-labelledby="ModalAddPublicKeyLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="ModalAddPublicKeyLabel">Add Public Key</h4>
            </div>
            <div class="modal-body">
                <form id="form-add-public-key" name="form-add-public-key">
                    <div class="form-group">
                        <label class="control-label" for="kid">Key Identifier&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
                        <input type="text" class="form-control" name="kid" id="kid">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="datepicker">Key validity range&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
                        <div class="input-daterange input-group" id="datepicker">
                            <input type="text" class="input-sm form-control" name="valid_from" />
                            <span class="input-group-addon">to</span>
                            <input type="text" class="input-sm form-control" name="valid_to" />
                        </div>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="active" name="active" checked>Is Active?
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="usage">Usage&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
                        {{ Form::select('usage', utils\ArrayUtils::convert2Assoc(\jwk\JSONWebKeyPublicKeyUseValues::$valid_uses) ,null , array('class' => 'form-control', 'id' => 'usage')) }}
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="pem_content">Key&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
                        <textarea class="form-control" rows="20" cols="40" name="pem_content" id="pem_content"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success save-public-key">Save</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    {{ HTML::script('assets/js/oauth2/profile/edit-client-public-keys.js') }}
@append