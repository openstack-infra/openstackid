<div class="row-fluid">
    <div class="span12">

        <div class="row-fluid">
            <div class="span6">
                <p style="color: #777777;max-width: 600px;">**Cannot contain a wildcard (http://*.example.com) or a path (http://example.com/subdir). </p>
                <form id="form-add-origin" name="form-add-origin" class="form-inline">
                    <label for="origin">New Allowed Origin&nbsp;<i class="icon-info-sign accordion-toggle" title="Uri schema must be under SSL"></i></label>
                    <input type="text" value="" id="origin" name="origin"/>
                    {{HTML::link(URL::action("ClientApiController@addAllowedOrigin",array("id"=>$client->id)),'Add',array('class'=>'btn add-origin-client','title'=>'Add a new Allowed Client Origin')) }}
                </form>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span12">
                <table id='table-origins' class="table table-hover table-condensed">
                    <thead>
                    <tr>
                        <th>Allowed Origin</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody id="body-allowed-origins">
                    @foreach ($allowed_origins as $origin)
                    <tr>
                        <td>{{ $origin->allowed_origin }}</td>
                        <td>&nbsp;{{ HTML::link(URL::action("ClientApiController@deleteClientAllowedOrigin",array("id"=>$client->id,'origin_id'=>$origin->id)),'Delete',array('class'=>'btn del-allowed-origin','title'=>'Deletes a Allowed Origin')) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <span id="info-origins" class="label label-info">** There are not any Registered Javascript Origins.</span>
            </div>
        </div>

    </div>
</div>

@section('scripts')
@parent
<script type="application/javascript">
	var clientOriginsUrls = {
		get :  '{{URL::action("ClientApiController@geAllowedOrigins",array("id"=>$client->id))}}',
		delete: '{{ URL::action("ClientApiController@deleteClientAllowedOrigin", array("id"=>$client->id,"uri_id"=>"@id")) }}'
	};
</script>
{{ HTML::script('assets/js/oauth2/profile/edit-client-allowed-origins.js') }}
@stop