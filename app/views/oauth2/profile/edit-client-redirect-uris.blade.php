<div class="row-fluid">
    <div class="span12">

        <div class="row-fluid">
            <div class="span12">
                <label for="redirect_uri">New Authorized Redirect Uri&nbsp;<i class="icon-info-sign accordion-toggle" title="Uri schema must be under SSL"></i></label>
                <input type="text" value="" id="redirect_uri" name="redirect_uri"/>
                {{HTML::link(URL::action("ClientApiController@addAllowedRedirectUri",array("id"=>$client->id)),'Add',array('class'=>'btn add-uri-client','title'=>'Add a new Registered Client Uri')) }}

            </div>
        </div>
        @if (count($allowed_uris)>0)
        <div class="row-fluid">
            <div class="span12">
                <table class="table table-hover table-condensed">
                    <thead>
                    <tr>
                        <th>Authorized Uri</th>
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
            </div>
        </div>
        @endif
    </div>
</div>
@section('scripts')
@parent
<script type="application/javascript">

    function loadAllowedClientUris(){
        var link = '{{URL::action("ClientApiController@getRegisteredUris",array("id"=>$client->id))}}';
        $.ajax(
            {
                type: "GET",
                url: link,
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    //load data...

                    var uris = data.allowed_uris;
                    var template = $('<tbody><tr><td class="uri-text"></td><td><a title="Deletes a Allowed Uri" class="btn del-allowed-uri">Delete</a></td></tr></tbody>');
                    var directives = {
                            'tr':{
                                'uri<-context':{
                                    'td.uri-text':'uri.uri',
                                    'a.del-allowed-uri@href':function(arg){
                                        var uri_id = arg.item.id;
                                        var href = '{{ URL::action("ClientApiController@deleteClientAllowedUri", array("id"=>$client->id,"uri_id"=>"-1")) }}';
                                        return href.replace('-1',uri_id);
                                    }

                                }
                            }
                    };
                    var html = template.render(uris, directives);
                    $('#body-allowed-uris').html(html.html());
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            }
        );
    }

    $(document).ready(function() {

        $("body").on('click',".add-uri-client",function(event){
            var link = $(this).attr('href');
            var data = {};
            data.redirect_uri = $('#redirect_uri').val();

            var regex_schema = /https.*/ig;
            var regex = /https:\/\/([\w@][\w.:@]+)\/?[\w\.?=%&=\-@/$,]*/ig;

            if(!regex_schema.test(data.redirect_uri)){
                displayAlert('Redirect Uri must under https schema!','.add-uri-client');
                event.preventDefault();
                return false;
            }

            if(!regex.test(data.redirect_uri)){
                displayAlert('Uri not valid!','.add-uri-client');
                event.preventDefault();
                return false;
            }

            $.ajax({
                type: "POST",
                url: link,
                dataType: "json",
                data: JSON.stringify(data),
                contentType: "application/json; charset=utf-8",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    $('#redirect_uri').val('');
                    loadAllowedClientUris();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            });
            event.preventDefault();
            return false;
        });

        $("body").on('click',".del-allowed-uri",function(event){

            if(confirm("Are you sure?")){
                var link = $(this).attr('href');
                $.ajax(
                    {
                        type: "DELETE",
                        url: link,
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                           loadAllowedClientUris();
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