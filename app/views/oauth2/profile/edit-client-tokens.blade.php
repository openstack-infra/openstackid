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

    function updateAccessTokenList(){
        //reload access tokens
        $.ajax(
            {
                type: "GET",
                url:'{{ URL::action("ClientApiController@getAccessTokens",array("id"=>$client->id))}}' ,
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    //load data...

                        if(data.access_tokens.length===0){
                            $('#table-access-tokens').hide();
                            $('#info-access-tokens').show();
                        }
                        else{
                            $('#info-access-tokens').hide();
                            $('#table-access-tokens').show();
                            var template   = $('<tbody><tr><td class="issued"></td><td class="scope"></td><td class="lifetime"></td><td><a title="Revoke Access Token" class="btn revoke-token revoke-access-token" data-hint="access-token">Revoke</a></td></tr></tbody>');
                            var directives = {
                                'tr':{
                                    'token<-context':{
                                        '@id'        :'token.value',
                                        'td.issued'  :'token.issued',
                                        'td.scope'   :'token.scope',
                                        'td.lifetime':'token.lifetime',
                                        'a@href':function(arg){
                                            var token_value = arg.item.value;
                                            var href = '{{ URL::action("ClientApiController@revokeToken",array("id"=>$client->id,"value"=>-1,"hint"=>"access-token")) }}';
                                            return href.replace('-1',token_value);
                                        },
                                        'a@data-value' :'token.value'
                                    }
                                }
                            };
                            var html = template.render(data.access_tokens, directives);
                            $('#body-access-tokens').html(html.html());
                        }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            });
    }

    function updateRefreshTokenList(){
        //reload access tokens
        $.ajax(
            {
                type: "GET",
                url:'{{ URL::action("ClientApiController@getRefreshTokens",array("id"=>$client->id))}}' ,
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    //load data...

                        if(data.refresh_tokens.length===0){
                            $('#table-refresh-tokens').hide();
                            $('#info-refresh-tokens').show();
                        }
                        else{
                            $('#info-refresh-tokens').hide();
                            $('#table-refresh-tokens').show();
                            var template   = $('<tbody><tr><td class="issued"></td><td class="scope"></td><td class="lifetime"></td><td><a title="Revoke Refresh Token" class="btn revoke-token revoke-refresh-token" data-hint="refresh-token">Revoke</a></td></tr></tbody>');
                            var directives = {
                                'tr':{
                                    'token<-context':{
                                        '@id'        :'token.value',
                                        'td.issued'  :'token.issued',
                                        'td.scope'   :'token.scope',
                                        'td.lifetime':function(arg){
                                            var token_lifetime = arg.item.lifetime;
                                            return token_lifetime===0?'Not Expire':token_lifetime;
                                        },
                                        'a@href':function(arg){
                                            var token_value = arg.item.value;
                                            var href = '{{ URL::action("ClientApiController@revokeToken",array("id"=>$client->id,"value"=>-1,"hint"=>"refresh-token")) }}';
                                            return href.replace('-1',token_value);
                                        },
                                        'a@data-value' :'token.value'
                                    }
                                }
                            };
                            var html = template.render(data.refresh_tokens, directives);
                            $('#body-refresh-tokens').html(html.html());
                            updateAccessTokenList();
                        }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            });
    }

    $(document).ready(function() {

        if($('#table-access-tokens tr').length===1){
            $('#info-access-tokens').show();
            $('#table-access-tokens').hide();
        }
        else{
            $('#info-access-tokens').hide();
            $('#table-access-tokens').show();
        }

        if($('#table-refresh-tokens tr').length===1){
            $('#info-refresh-tokens').show();
            $('#table-refresh-tokens').hide();
        }
        else{
            $('#info-refresh-tokens').hide();
            $('#table-refresh-tokens').show();
        }

        $("body").on('click','.refresh-refresh-tokens',function(event){
            updateRefreshTokenList();
            event.preventDefault();
            return false;
        });

        $("body").on('click','.refresh-access-tokens',function(event){
            updateAccessTokenList();
            event.preventDefault();
            return false;
        });

        $("body").on('click',".revoke-token",function(event){

            var link        = $(this);
            var value       = link.attr('data-value');
            var hint        = link.attr('data-hint');
            var url         = link.attr('href');
            var table_id    = hint ==='refresh-token'? 'table-refresh-tokens':'table-access-tokens';
            var info_id     = hint ==='refresh-token'? 'info-refresh-tokens':'info-access-tokens';
            var confirm_msg = hint ==='refresh-token'? 'Are you sure?, revoking this refresh token also will become void all related Access Tokens':'Are you sure?';
            if(confirm(confirm_msg)){

                $.ajax(
                    {
                        type: "DELETE",
                        url: url,
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            //load data...
                            var row = $('#'+value);
                            row.remove();
                            var row_qty = $('#'+table_id+' tr').length;
                            if(row_qty===1){ //only we have the header ...
                                $('#'+table_id).hide();
                                $('#'+info_id).show();
                            }
                            if(hint=='refresh-token'){
                                updateAccessTokenList();
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            ajaxError(jqXHR, textStatus, errorThrown);
                        }
                    }
                );
            }
            event.preventDefault();
            return false;
        });
    });
</script>
@stop






