<div class="row-fluid">
    <div class="span12">

        <div class="row-fluid">
            <div class="span12">
                <label for="client_id" class="label-client-secret">Client ID</label>
                <span id="client_id">{{ $client->client_id }}</span>
            </div>
        </div>
        @if($client->client_type == oauth2\models\IClient::ClientType_Confidential)
        <div class="row-fluid">
            <div class="span12">
                <label for="client_secret" class="label-client-secret">Client Secret</label>
                <span id="client_secret">{{ $client->client_secret }}</span>
                {{ HTML::link(URL::action("ClientApiController@regenerateClientSecret",array("id"=>$client->id)),'Regenerate',array('class'=>'btn regenerate-client-secret','title'=>'Regenerates Client Secret')) }}
            </div>
        </div>
        @if($client->application_type == oauth2\models\IClient::ApplicationType_Web_App)
        <div class="row-fluid">
            <div class="span12">
                <label class="label-client-secret">Client Settings</label>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <label class="checkbox">
                    <input type="checkbox"
                    @if ($client->use_refresh_token)
                    checked
                    @endif
                    id="use-refresh-token">
                    Use Refresh Tokens
                    &nbsp;<i class="icon-info-sign accordion-toggle" title=""></i>
                </label>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <label class="checkbox">
                    <input type="checkbox"
                    @if ($client->rotate_refresh_token)
                    checked
                    @endif
                    id="use-rotate-refresh-token-policy">
                    Use Rotate Refresh Token Policy
                    &nbsp;<i class="icon-info-sign accordion-toggle" title=""></i>
                </label>
            </div>
        </div>
        @endif
        @endif
    </div>
</div>

@section('scripts')
@parent
<script type="application/javascript">
  var dataClientUrls = {
	  refresh: '{{URL::action("ClientApiController@setRefreshTokenClient",array("id"=>$client->id))}}',
	  rotate: '{{URL::action("ClientApiController@setRotateRefreshTokenPolicy",array("id"=>$client->id))}}'
  };
</script>
{{ HTML::script('js/oauth2/profile/edit-client-data.js') }}
@stop