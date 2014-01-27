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
    </div>
</div>
@section('scripts')
@parent
<script type="application/javascript">
    $(document).ready(function() {

        $("body").on('click',".regenerate-client-secret",function(event){
            if(confirm("Are you sure? Regenerating client secret would invalidate all current tokens")){
                var link = $(this).attr('href');
                $.ajax(
                    {
                        type: "GET",
                        url: link,
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            //load data...
                            $('#client_secret').text(data.new_secret);
                            //clean token UI
                            $('#table-access-tokens').remove();
                            $('#table-refresh-tokens').remove();
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            var HTTP_status = jqXHR.status;
                            if(HTTP_status!=200){
                                response = $.parseJSON(jqXHR.responseText);
                                alert(response.error);
                            }
                            else{
                                alert('server error');
                            }
                        }
                    }
                );
            }
            event.preventDefault();
            return false;
        });

        $("body").on('click',"#use-refresh-token",function(event){
            var param = {};
            param.use_refresh_token  = $(this).is(':checked');
            $.ajax(
                {
                    type: "PUT",
                    url: '{{URL::action("ClientApiController@setRefreshTokenClient",array("id"=>$client->id))}}',
                    data: JSON.stringify(param),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        var HTTP_status = jqXHR.status;
                        if(HTTP_status!=200){
                            response = $.parseJSON(jqXHR.responseText);
                            alert(response.error);
                        }
                        else{
                            alert('server error');
                        }
                    }
                }
            );
        });

        $("body").on('click',"#use-rotate-refresh-token-policy",function(event){
            var param = {};
            param.rotate_refresh_token  = $(this).is(':checked');
            $.ajax(
                {
                    type: "PUT",
                    url: '{{URL::action("ClientApiController@setRotateRefreshTokenPolicy",array("id"=>$client->id))}}',
                    data: JSON.stringify(param),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        var HTTP_status = jqXHR.status;
                        if(HTTP_status!=200){
                            response = $.parseJSON(jqXHR.responseText);
                            alert(response.error);
                        }
                        else{
                            alert('server error');
                        }
                    }
                }
            );
        });
    });
</script>
@stop
