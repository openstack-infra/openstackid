@section('css')
    {!! HTML::style('assets/css/edit-client-public-keys.css') !!}
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
    <tr id="tr_{!!$public_key->id!!}">
        <td width="7%">
            <div class="row">
                <div class="col-md-6">
                    <span data-public-key-id="{!!$public_key->id!!}" class="badge public-key-status {!! $public_key->active ? 'public-key-active':'public-key-deactivated' !!}" title="{!! $public_key->active ? 'active':'deactivated' !!}">&nbsp;</span>
                </div>
                <div class="col-md-6 col-md-offset-neg-1">
                    <i class="fa fa-key fa-2x pointable" title="{!!$public_key->kid!!}&nbsp;({!!$public_key->type!!})"></i>
                </div>
            </div>
        </td>
        <td colspan="3">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <strong class="public-key-title">{!!$public_key->kid!!}&nbsp;<span class="badge public-key-usage pointable" title="Key Usage">{!!$public_key->usage!!}</span>&nbsp;<span class="label label-info pointable" title="Key Type">{!!$public_key->type!!}</span></strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <code class="public-key-fingerprint">{!!$public_key->getSHA_256_Thumbprint()!!}</code>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <span class="public-key-validity-range">valid from <strong>{!!$public_key->valid_from!!}</strong> to <strong>{!!$public_key->valid_to!!}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </td>
        <td><a class="btn btn-default btn-sm active delete-public-key" href="#" data-public-key-id="{!!$public_key->id!!}">Delete</a></td>
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

@include('modal', array ('modal_id' => 'ModalAddPublicKey', 'modal_title' => 'Add Public Key', 'modal_save_css_class' => 'save-public-key', 'modal_save_text' => 'Save', 'modal_form' => 'oauth2.profile.edit-client-public-key-add-form', 'modal_form_data' => array()))

@section('scripts')
    {!! HTML::script('assets/js/oauth2/profile/edit-client-public-keys.js') !!}
@append