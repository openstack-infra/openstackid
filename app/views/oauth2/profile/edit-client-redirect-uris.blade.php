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
                    if(uris.length>0){
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
                        $('#info-uris').hide();
                        $('#table-uris').show();
                    }
                    else{
                        $('#info-uris').show();
                        $('#table-uris').hide();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            }
        );
    }

    $(document).ready(function() {

        if($('#table-uris tr').length===1){
            $('#info-uris').show();
            $('#table-uris').hide();
        }
        else{
            $('#info-uris').hide();
            $('#table-uris').show();
        }

        var form_add_redirect_uri = $('#form-add-uri');

        var add_redirect_uri_validator = form_add_redirect_uri.validate({
            rules: {
                "redirect_uri"  :{required: true, ssl_uri: true}
            }
        });

        $("body").on('click',".add-uri-client",function(event){
            var is_valid = form_add_redirect_uri.valid();
            if (is_valid){
                var link = $(this).attr('href');
                var uri = form_add_redirect_uri.serializeForm();
                form_add_redirect_uri.cleanForm();
                add_redirect_uri_validator.resetForm();
                $.ajax({
                    type: "POST",
                    url: link,
                    dataType: "json",
                    data: JSON.stringify(uri),
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
            }
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