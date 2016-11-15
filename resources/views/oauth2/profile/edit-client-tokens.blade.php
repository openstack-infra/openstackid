<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12">
                <h5>Issued Access Tokens&nbsp;<span class="glyphicon glyphicon-refresh accordion-toggle refresh-access-tokens" aria-hidden="true" title="Update Access Tokens List"></span></h5>
                <hr/>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
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
                                <tr id="{!! $access_token->value !!}">
                                    <td>{!! $access_token->created_at !!}</td>
                                    <td>{!! $access_token->scope !!}</td>
                                    <td>{!! $access_token->getRemainingLifetime() !!}</td>
                                    <td>{!! HTML::link(URL::action("Api\ClientApiController@revokeToken",array("id"=>$client->id,"value"=>$access_token->value,'hint'=>'access-token')),'Revoke',array('class'=>'btn btn-default btn-md active btn-delete revoke-token revoke-access-token','title'=>'Revoke Access Token','data-value'=>$access_token->value,'data-hint'=>'access-token')) !!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <ul class="pagination" id="access_token_paginator">
                             <?php for($i = 0 ; $i < $access_tokens_pages ; $i++){  ?>
                            <li><a class="access_token_page" href="#" data-page-nbr="{!! $i+1 !!}">{!! $i+1 !!}</a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <span id="info-access-tokens" class="label label-info">** There are not any Access Tokens granted for this application.</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h5>Issued Refresh Tokens&nbsp;<span class="glyphicon glyphicon-refresh accordion-toggle refresh-refresh-tokens" title="Update Refresh Tokens List"></span></h5>
                <hr/>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
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
                                <tr id="{!! $refresh_token->value !!}">
                                    <td>{!! $refresh_token->created_at !!}</td>
                                    <td>{!! $refresh_token->scope !!}</td>
                                    @if($refresh_token->getRemainingLifetime()===0)
                                        <td>Not Expire</td>
                                    @else
                                        <td>{!! $refresh_token->getRemainingLifetime() !!}</td>
                                    @endif
                                    <td>{!! HTML::link(URL::action("Api\ClientApiController@revokeToken",array("id"=>$client->id,"value"=>$refresh_token->value,'hint'=>'refresh-token')),'Revoke',array('class'=>'btn btn-default btn-md active btn-delete revoke-token revoke-refresh-token','title'=>'Revoke Refresh Token','data-value'=>$refresh_token->value,'data-hint'=>'refresh-token')) !!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <ul class="pagination" id="refresh_token_paginator">
                            <?php for($i = 0 ; $i < $refresh_tokens_pages ; $i++){  ?>
                            <li><a class="refresh_token_page" href="#" data-page-nbr="{!! $i+1 !!}">{!! $i+1 !!}</a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <span id="info-refresh-tokens" class="label label-info">** There are not any Refresh Tokens granted for this application.</span>
            </div>
        </div>
    </div>
</div>
@section('scripts')
<script type="application/javascript">
	var TokensUrls = {
		AccessTokenUrls : {
			get : '{!! URL::action("Api\ClientApiController@getAccessTokens",array("id"=>$client->id))!!}',
			delete :'{!! URL::action("Api\ClientApiController@revokeToken",array("id"=>$client->id,"value"=>-1,"hint"=>"access-token")) !!}'
		},
		RefreshTokenUrl : {
			get: '{!! URL::action("Api\ClientApiController@getRefreshTokens",array("id"=>$client->id))!!}',
			delete : '{!! URL::action("Api\ClientApiController@revokeToken",array("id"=>$client->id,"value"=>-1,"hint"=>"refresh-token")) !!}'
		}
	};
</script>
{!! HTML::script('assets/js/oauth2/profile/edit-client-tokens.js') !!}
@append