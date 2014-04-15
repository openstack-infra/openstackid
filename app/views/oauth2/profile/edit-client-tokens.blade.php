<div class="row-fluid">
    <div class="row-fluid">
        <h4 style="float:left">Issued Access Tokens</h4>
        <div style="position: relative;float:left;">
            <div style="position:absolute;top:13px;margin-left:5px"><i class="icon-refresh accordion-toggle refresh-access-tokens" title="Update Access Tokens List"></i></div>
        </div>
    </div>
     <table id='table-access-tokens' class="table table-hover table-condensed">
        <thead>
        <tr>
            <th><i class="icon-info-sign accordion-toggle" title="Time is on UTC"></i>&nbsp;Issued</th>
            <th>Scopes</th>
            <th><i class="icon-info-sign accordion-toggle" title="Lifetime is on seconds"></i>&nbsp;Remaining Lifetime</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody id="body-access-tokens">
            @foreach ($access_tokens as $access_token)
            <tr id="{{ $access_token->value }}">
                <td>{{ $access_token->created_at }}</td>
                <td>{{ $access_token->getFriendlyScopes() }}</td>
                <td>{{ $access_token->getRemainingLifetime() }}</td>
                <td>{{ HTML::link(URL::action("ClientApiController@revokeToken",array("id"=>$client->id,"value"=>$access_token->value,'hint'=>'access-token')),'Revoke',array('class'=>'btn revoke-token revoke-access-token','title'=>'Revoke Access Token','data-value'=>$access_token->value,'data-hint'=>'access-token')) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <span id="info-access-tokens" class="label label-info">** There are not any Access Tokens granted for this application.</span>
    <div class="row-fluid">
        <h4 style="float:left">Issued Refresh Tokens</h4>
        <div style="position: relative;float:left;">
            <div style="position:absolute;top:13px;margin-left:5px"><i class="icon-refresh accordion-toggle refresh-refresh-tokens" title="Update Refresh Tokens List"></i></div>
        </div>
    </div>
    <table id='table-refresh-tokens' class="table table-hover table-condensed">
        <thead>
        <tr>
            <th><i class="icon-info-sign accordion-toggle" title="Time is on UTC"></i>&nbsp;Issued</th>
            <th>Scopes</th>
            <th><i class="icon-info-sign accordion-toggle" title="Lifetime is on seconds"></i>&nbsp;Remaining Lifetime</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody id="body-refresh-tokens">
        @foreach ($refresh_tokens as $refresh_token)
        <tr id="{{ $refresh_token->value }}">
            <td>{{ $refresh_token->created_at }}</td>
            <td>{{ $refresh_token->getFriendlyScopes() }}</td>
            @if($refresh_token->getRemainingLifetime()===0)
                <td>Not Expire</td>
            @else
                <td>{{ $refresh_token->getRemainingLifetime() }}</td>
            @endif
            <td>{{ HTML::link(URL::action("ClientApiController@revokeToken",array("id"=>$client->id,"value"=>$refresh_token->value,'hint'=>'refresh-token')),'Revoke',array('class'=>'btn revoke-token revoke-refresh-token','title'=>'Revoke Refresh Token','data-value'=>$refresh_token->value,'data-hint'=>'refresh-token')) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <span id="info-refresh-tokens" class="label label-info">** There are not any Refresh Tokens granted for this application.</span>
</div>
@section('scripts')
@parent
<script type="application/javascript">
	var TokensUrls = {
		AccessTokenUrls : {
			get : '{{ URL::action("ClientApiController@getAccessTokens",array("id"=>$client->id))}}',
			delete :'{{ URL::action("ClientApiController@revokeToken",array("id"=>$client->id,"value"=>-1,"hint"=>"access-token")) }}'
		},
		RefreshTokenUrl : {
			get: '{{ URL::action("ClientApiController@getRefreshTokens",array("id"=>$client->id))}}',
			delete : '{{ URL::action("ClientApiController@revokeToken",array("id"=>$client->id,"value"=>-1,"hint"=>"refresh-token")) }}'
		}
	};
</script>
{{ HTML::script('js/oauth2/profile/edit-client-tokens.js') }}
@stop