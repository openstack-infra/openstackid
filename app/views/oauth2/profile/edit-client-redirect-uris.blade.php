<div class="row-fluid">
    <div class="span12">

        <div class="row-fluid">
            <div class="span6">
                <p style="color: #777777;max-width: 600px;">** Redirect Uris they must been under SSL schema. </p>
                <form id="form-add-uri" name="form-add-uri" class="form-inline">
                    <label for="redirect_uri">New Authorized redirect URI&nbsp;<i class="icon-info-sign accordion-toggle" title="Uri schema must be under SSL"></i></label>
                    <input type="text" value="" id="redirect_uri" name="redirect_uri"/>
                    {{HTML::link(URL::action("ClientApiController@addAllowedRedirectUri",array("id"=>$client->id)),'Add',array('class'=>'btn add-uri-client','title'=>'Add a new Registered Client Uri')) }}
                </form>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span12">
                <table id='table-uris' class="table table-hover table-condensed">
                    <thead>
                    <tr>
                        <th>Authorized URI</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody id="body-allowed-uris">
                    @foreach ($allowed_uris as $uri)
                    <tr>
                        <td>{{ $uri->uri }}</td>
                        <td>&nbsp;{{ HTML::link(URL::action("ClientApiController@deleteClientAllowedUri",array("id"=>$client->id,'uri_id'=>$uri->id)),'Delete',array('class'=>'btn del-allowed-uri','title'=>'Deletes a Allowed Uri')) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <span id="info-uris" class="label label-info">** There are not any Authorized Redirect URIs.</span>
            </div>
        </div>
    </div>
</div>
@section('scripts')
@parent
<script type="application/javascript">
	var clientUrisUrls = {
		get : '{{URL::action("ClientApiController@getRegisteredUris",array("id"=>$client->id))}}',
		delete :'{{ URL::action("ClientApiController@deleteClientAllowedUri", array("id"=>$client->id,"uri_id"=>"-1")) }}'
	};
</script>
{{ HTML::script('js/oauth2/profile/edit-client-redirect-uris.js') }}
@stop