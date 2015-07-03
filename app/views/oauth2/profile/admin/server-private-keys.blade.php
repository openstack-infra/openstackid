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
                                    <strong class="private-key-title">{{$private_key->kid}}&nbsp;<span class="badge private-key-usage pointable" title="use: identifies the intended use of the public key">{{$private_key->usage}}</span>&nbsp;<span class="label label-info pointable" title="Key Type">{{$private_key->type}}</span>&nbsp;<span class="label label-primary pointable" title="alg: identifies the algorithm intended for use with the key">{{$private_key->alg}}</span></strong>
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

    @include('modal', array ('modal_id' => 'ModalAddPrivateKey', 'modal_title' => 'Add Private Key', 'modal_save_css_class' => 'save-private-key', 'modal_save_text' => 'Save', 'modal_form' => 'oauth2.profile.admin.server-private-key-add-form', 'modal_form_data' => array()))
@stop