@extends('layout')

@section('title')
<title>Welcome to openstackId - Edit Registered Application</title>
@stop

@section('content')
<a href="{{ URL::previous() }}">Go Back</a>

<legend>{{ $client->app_name }}</legend>

@if($errors->any())
<div class="errors">
    <ul>
        @foreach($errors->all() as $error)
        <div class="alert alert-error">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ $error }}
        </div>
        @endforeach
    </ul>
</div>
@endif
<div id="accordion">
    <h3><i class="icon-info-sign accordion-toggle" title="OAuth2 Client ID and Client Secret"></i>&nbsp;OAuth 2.0 Client ID</h3>
    <div class="row-fluid">
        <div class="span12">

            <div class="row-fluid">
                <div class="span12">
                    <label for="client_id" class="label-client-secret">Client ID</label>
                    <span id="client_id">{{ $client->client_id }}</span>
                </div>
            </div>
            @if($client->client_type === oauth2\models\IClient::ClientType_Confidential)
            <div class="row-fluid">
                <div class="span12">
                    <label for="client_secret" class="label-client-secret">Client Secret</label>
                    <span id="client_secret">{{ $client->client_secret }}</span>
                    {{ HTML::link(URL::action("UserController@getRegenerateClientSecret",array("id"=>$client->id)),'Regenerate',array('class'=>'btn regenerate-client-secret','title'=>'Regenerates Client Secret')) }}
                </div>
            </div>
            @endif
        </div>
    </div>
    <h3><i class="icon-info-sign accordion-toggle" title="Authorized Client Redirection Uris"></i>&nbsp;Authorized Redirection Uris</h3>
    <div class="row-fluid">
        <div class="span12">

            <div class="row-fluid">
                <div class="span12">
                        <label for="redirect_uri">New Authorized Redirect Uri&nbsp;<i class="icon-info-sign accordion-toggle" title="Uri schema must be under SSL"></i></label>
                        <input type="text" value="" id="redirect_uri" name="redirect_uri"/>
                        {{ HTML::link(URL::action("UserController@postAddAllowedRedirectUri",array("id"=>$client->id)),'Add',array('class'=>'btn add-uri-client','title'=>'Add a new Registered Client Uri')) }}
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
                            <td>&nbsp;{{ HTML::link(URL::action("UserController@getDeleteClientAllowedUri",array("id"=>$client->id,'uri_id'=>$uri->id)),'Delete',array('class'=>'btn del-allowed-uri','title'=>'Deletes a Allowed Uri')) }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
    <h3><i class="icon-info-sign accordion-toggle" title="Application Allowed Scopes"></i>&nbsp;Application Allowed Scopes</h3>
    <div class="row-fluid">
        <div class="span12">
            <ul class="unstyled list-inline">
            @foreach ($scopes as $scope)
                <li>
                    <label class="checkbox">
                            <input type="checkbox" class="scope-checkbox" id="scope[]"
                            @if ( in_array($scope->id,$selected_scopes))
                                checked
                            @endif
                            value="{{$scope->id}}"/>{{$scope->name}}&nbsp;<i class="icon-info-sign accordion-toggle" title="{{$scope->description}}"></i>
                    </label>
                </li>
            @endforeach
            </ul>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {

        $( "#accordion" ).accordion({
            collapsible: true,
            heightStyle: "content"
        });

        function displayAlert(msg,after){
            var alert = $('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>'+msg+'</div>');
            alert.insertAfter(after);
        }

        function loadAllowedClientUris(){
            var link = '{{URL::action("UserController@getRegisteredClientUris",array("id"=>$client->id))}}';
            $.ajax(
                {
                    type: "GET",
                    url: link,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                        if(data.status==='OK'){
                            var uris = data.allowed_uris;
                            var template = $('<tbody><tr><td class="uri-text"></td><td><a title="Deletes a Allowed Uri" class="btn del-allowed-uri">Delete</a></td></tr></tbody>');
                            var directives = {
                                'tr':{
                                    'uri<-context':{
                                        'td.uri-text':'uri.redirect_uri',
                                        'a.del-allowed-uri@href':function(arg){
                                            var uri_id = arg.item.id;
                                            var href = '{{ URL::action("UserController@getDeleteClientAllowedUri", array("id"=>$client->id,"uri_id"=>"-1")) }}';
                                            return href.replace('-1',uri_id);
                                        }

                                    }
                                }
                            };
                            var html = template.render(uris, directives);
                            //alert(html.html());
                            $('#body-allowed-uris').html(html.html());
                        }
                        else{
                            alert('There was an error!');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert( "Request failed: " + textStatus );
                    }
                }
            );
        }

        $("body").on('click',".regenerate-client-secret",function(event){
            var link = $(this).attr('href');
            $.ajax(
                {
                    type: "GET",
                    url: link,
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                        if(data.status==='OK'){
                            $('#client_secret').text(data.new_secret);
                        }
                        else{
                            alert('There was an error!');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert( "Request failed: " + textStatus );
                    }
                }
            );
            event.preventDefault();
            return false;
        });

        $("body").on('click',".add-uri-client",function(event){
            var link = $(this).attr('href');
            var data = {};
            data.redirect_uri = $('#redirect_uri').val();

            var regex = /https:\/\/([\w@][\w.:@]+)\/?[\w\.?=%&=\-@/$,]*/ig;

            if(regex.test(data.redirect_uri)){
                $.ajax(
                    {
                        type: "POST",
                        url: link,
                        dataType: "json",
                        data: JSON.stringify(data),
                        contentType: "application/json; charset=utf-8",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            //load data...
                            if(data.status==='OK'){
                                $('#redirect_uri').val('');
                                loadAllowedClientUris();
                            }
                            else{
                                displayAlert(data.msg,'.add-uri-client');
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            alert( "Request failed: " + textStatus );
                        }
                    }
                );
            }
            else{
                displayAlert('Uri not valid!','.add-uri-client');
            }
            event.preventDefault();
            return false;
        });

        $("body").on('click',".del-allowed-uri",function(event){

            if(confirm("Are you sure?")){
                var link = $(this).attr('href');
                $.ajax(
                    {
                        type: "GET",
                        url: link,
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            //load data...
                            if(data.status==='OK'){
                                loadAllowedClientUris();
                            }
                            else{
                                alert('There was an error!');
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            alert( "Request failed: " + textStatus );
                        }
                    }
                );
            }
            event.preventDefault();
            return false;
        });

        $("body").on('click',".scope-checkbox",function(event){
            var scope = {};
            scope.scope_id = $(this).attr('value');
            scope.checked = $(this).is(':checked');
            $.ajax(
                {
                    type: "POST",
                    url: '{{URL::action("UserController@postAddAllowedScope",array("id"=>$client->id))}}',
                    data: JSON.stringify(scope),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    timeout:60000,
                    success: function (data,textStatus,jqXHR) {
                        //load data...
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert( "Request failed: " + textStatus );
                    }
                }
            );
        });
    });
</script>
@stop