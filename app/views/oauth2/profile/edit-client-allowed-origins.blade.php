<div class="row-fluid">
    <div class="span12">

        <div class="row-fluid">
            <div class="span6">
                <form id="form-add-origin" name="form-add-origin" class="form-inline">
                    <label for="origin">New Allowed Origin&nbsp;<i class="icon-info-sign accordion-toggle" title="Uri schema must be under SSL"></i></label>
                    <input type="text" value="" id="origin" name="origin"/>
                    {{HTML::link(URL::action("ClientApiController@addAllowedOrigin",array("id"=>$client->id)),'Add',array('class'=>'btn add-origin-client','title'=>'Add a new Allowed Client Origin')) }}
                </form>
            </div>
        </div>
        @if (count($allowed_origins)>0)
        <div class="row-fluid">
            <div class="span12">
                <table class="table table-hover table-condensed">
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
            </div>
        </div>
        @endif
    </div>
</div>
@section('scripts')
@parent
<script type="application/javascript">

    function loadAllowedClientOrigin(){
        var link = '{{URL::action("ClientApiController@geAllowedOrigins",array("id"=>$client->id))}}';
        $.ajax(
            {
                type: "GET",
                url: link,
                dataType: "json",
                timeout:60000,
                success: function (data,textStatus,jqXHR) {
                    //load data...

                    var origins = data.allowed_origins;
                    var template = $('<tbody><tr><td class="origin-text"></td><td><a title="Deletes a Allowed Origin" class="btn del-allowed-origin">Delete</a></td></tr></tbody>');
                    var directives = {
                        'tr':{
                            'origin<-context':{
                                'td.origin-text':'origin.allowed_origin',
                                'a.del-allowed-origin@href':function(arg){
                                    var origin_id = arg.item.id;
                                    var href = '{{ URL::action("ClientApiController@deleteClientAllowedOrigin", array("id"=>$client->id,"uri_id"=>"@id")) }}';
                                    return href.replace('@id',origin_id);
                                }

                            }
                        }
                    };
                    var html = template.render(origins, directives);
                    $('#body-allowed-origins').html(html.html());
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    ajaxError(jqXHR, textStatus, errorThrown);
                }
            }
        );
    }

    $(document).ready(function() {


        var form_add_origin = $('#form-add-origin');

        var add_origin_validator = form_add_origin.validate({
            rules: {
                "origin"  :{required: true, ssl_uri: true}
            }
        });

        $("body").on('click',".add-origin-client",function(event){
            var is_valid = form_add_origin.valid();
            if (is_valid){
                var link = $(this).attr('href');
                var origin = form_add_origin.serializeForm();
                form_add_origin.cleanForm();
                add_origin_validator.resetForm();
                $.ajax({
                    type: "POST",
                    url: link,
                    dataType: "json",
                    data: JSON.stringify(origin),
                    contentType: "application/json; charset=utf-8",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        $('#origin').val('');
                        loadAllowedClientOrigin();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        ajaxError(jqXHR, textStatus, errorThrown);
                    }
                });
            }
            event.preventDefault();
            return false;
        });

        $("body").on('click',".del-allowed-origin",function(event){

            if(confirm("Are you sure?")){
                var link = $(this).attr('href');
                $.ajax(
                    {
                        type: "DELETE",
                        url: link,
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            loadAllowedClientOrigin();
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