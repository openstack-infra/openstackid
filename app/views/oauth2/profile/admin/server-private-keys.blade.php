@extends('layout')
@section('title')
    <title>Welcome to openstackId - Server Admin - Server Private Keys</title>
@stop
@section('css')
    {{ HTML::style('bower_assets/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}
    {{ HTML::style('assets/css/private-keys.css') }}
@append
@section('scripts')
    {{ HTML::script('bower_assets/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}
    {{ HTML::script('bower_assets/pwstrength-bootstrap/dist/pwstrength-bootstrap-1.2.7.min.js')}}
    {{ HTML::script('assets/js/oauth2/profile/admin/server-private-keys.js') }}

    <script type="application/javascript">
        var privateKeyUrls = {
            add: '{{URL::action("ServerPrivateKeyApiController@create")}}',
            get: '{{URL::action("ServerPrivateKeyApiController@getByPage")}}',
            delete: '{{URL::action("ServerPrivateKeyApiController@delete",array("id" =>'@id'))}}',
            update: '{{URL::action("ServerPrivateKeyApiController@update",array('public_key_id'=> '@id'))}}'
        };
    </script>
@append
@section('content')
    @include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))

    <table id="private-keys-table" class="table">
        <caption>
        </caption>
        <thead>
        <tr style="background-color: #f5f5f5;">
            <td width="90%" colspan="4">
                <h5 style="font-weight: bold">Private keys&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="OIDC Server private keys for encryption/signing"></span></h5>
            </td>
            <td width="10%">
                <a href="#" class="btn btn-default btn-sm active add-private-key">Add Private Key</a>
            </td>
        </tr>
        <tr>
            <td colspan="5">
                <p>This is a list of Private Keys keys associated with the server. Remove any keys that you do not recognize.</p>
            </td>
        </tr>
        </thead>
        <tbody id="body-private-keys">
        @foreach ($private_keys as $private_key)
            <tr id="tr_{{$private_key->id}}">
                <td width="7%">
                    <div class="row">
                        <div class="col-md-6">
                            <span data-private-key-id="{{$private_key->id}}" class="badge private-key-status {{ $private_key->active ? 'private-key-active':'private-key-deactivated' }}" title="{{ $private_key->active ? 'active':'deactivated' }}">&nbsp;</span>
                        </div>
                        <div class="col-md-6 col-md-offset-neg-1">
                            <i class="fa fa-key fa-2x pointable" title="{{$private_key->kid}}&nbsp;({{$private_key->type}})"></i>
                        </div>
                    </div>
                </td>
                <td colspan="3">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <strong class="private-key-title">{{$private_key->kid}}&nbsp;<span class="badge private-key-usage pointable" title="Key Usage">{{$private_key->usage}}</span>&nbsp;<span class="label label-info pointable" title="Key Type">{{$private_key->type}}</span></strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <code class="private-key-fingerprint">{{$private_key->getSHA_256_Thumbprint()}}</code>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <span class="private-key-validity-range">valid from <strong>{{$private_key->valid_from}}</strong> to <strong>{{$private_key->valid_to}}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
                <td><a class="btn btn-default btn-sm active delete-private-key btn-delete" href="#" data-private-key-id="{{$private_key->id}}">Delete</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="alert alert-danger private-keys-empty-message" role="alert"
         @if(count($private_keys) > 0 )
         style="display: none"
            @endif
            >
        <p>There are no Private keys yet.</p>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="ModalAddPrivateKey" tabindex="-1" role="dialog" aria-labelledby="ModalAddPrivateKeyLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="ModalAddPrivateKeyLabel">Add Private Key</h4>
                </div>
                <div class="modal-body">
                    <form id="form-add-private-key" name="form-add-private-key">
                        <div class="form-group">
                            <label class="control-label" for="kid">Key Identifier&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="The 'kid' (key ID) parameter is used to match a specific key. This is used, for instance, to choose among a set of keys within a JWK Set during key rollover. The structure of the 'kid' value is unspecified.  When 'kid' values are used within a JWK Set, different keys within the JWK Set SHOULD use distinct 'kid' values.  (One example in which different keys might use the same 'kid' value is if they have different 'kty' (key type) values but are considered to be equivalent alternatives by the application using them.)"></span></label>
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
                                <input type="checkbox" id="active" name="active" checked>Is Active?&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="usage">Usage&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="The 'use' parameter identifies the intended use of the key."></span></label>
                            {{ Form::select('usage', utils\ArrayUtils::convert2Assoc(\jwk\JSONWebKeyPublicKeyUseValues::$valid_uses) ,null , array('class' => 'form-control', 'id' => 'usage')) }}
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="password">Key Password&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
                            <input type="password" class="form-control" name="password" id="password" maxlength="255">
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="password-confirmation">Key Confirmation Password&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
                            <input type="password" class="form-control" name="password-confirmation" id="password-confirmation" maxlength="255">
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="autogenerate" name="autogenerate" checked>Should Autogenerate?&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title="If checkeck, private key value will be autogenerated with a key length of 2048 bits."></span>
                            </label>
                        </div>
                        <div class="form-group" id="pem_container" style="display:none">
                            <label class="control-label" for="pem_content">Key&nbsp;<span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true" title=""></span></label>
                            <textarea class="form-control" rows="10" cols="40" name="pem_content" id="pem_content"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success save-private-key">Save</button>
                </div>
            </div>
        </div>
    </div>
@stop