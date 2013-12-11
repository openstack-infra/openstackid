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
    <h3>OAuth 2.0 Client ID</h3>
    <div class="row-fluid">
        <div class="span12">

            <div class="row-fluid">
                <div class="span12">
                    <label for="client_id" class="label-client-secret">Client ID</label>
                    <span id="client_id">{{ $client->client_id }}</span>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    <label for="client_secret" class="label-client-secret">Client Secret</label>
                    <span id="client_secret">{{ $client->client_secret }}</span>
                    {{ HTML::link(URL::action("UserController@getRegenerateClientSecret",array("id"=>$client->id)),'Regenerate',array('class'=>'btn regenerate-client-secret','title'=>'Regenerates Client Secret')) }}
                </div>
            </div>
        </div>
    </div>
    <h3>Allowed Redirect Uris</h3>
    <div class="row-fluid">
        <div class="span12">

            <div class="row-fluid">
                <div class="span12">
                    {{ Form::open(array('url' => URL::action('UserController@postAddAllowedRedirectUri'), 'method' => 'post')) }}
                        <label for="redirect_uri">New Allowed Redirect Uri</label>
                        <input type="text" value="" id="redirect_uri" name="redirect_uri"/>
                        <input type="hidden" value="{{ $client->id }}" id="client_id" name="client_id"/>
                        {{ Form::submit('Add',array('id'=>'add_uri','class'=>'btn')) }}
                    {{ Form::close() }}
                </div>
            </div>
            @if (count($allowed_uris)>0)
            <div class="row-fluid">
                <div class="span12">
                    <table class="table table-hover table-condensed">
                        <thead>
                        <tr>
                            <th>Allowed Uri</th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
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
    <h3>Allowed Scopes</h3>
    <div class="row-fluid">
        <div class="span12">

            <ul class="unstyled list-inline">
            @foreach ($scopes as $scope)
                <li>
                    <label class="checkbox">
                        @if ( in_array($scope->id,$selected_scopes))
                            <input type="checkbox" class="scope-checkbox" id="scope[]" checked value="{{$scope->id}}"/>{{$scope->short_description}}<i class="icon-info-sign accordion-toggle" title="{{$scope->description}}"></i>
                        @else
                            <input type="checkbox" class="scope-checkbox" id="scope[]" value="{{$scope->id}}"/>{{$scope->short_description}}<i class="icon-info-sign accordion-toggle" title="{{$scope->description}}"></i>
                        @endif
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
        $( "#accordion" ).accordion();
        $("body").on('click',".scope-checkbox",function(event){
            var scope = {};
            scope.id = $(this).attr('value');
            scope.checked = $(this).is(':checked');
            scope.client_id = {{ $client->id }};
            $.ajax(
                {
                    type: "POST",
                    url: '{{URL::action("UserController@postAddAllowedScope",null)}}',
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