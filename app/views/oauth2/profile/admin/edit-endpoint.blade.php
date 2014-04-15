@extends('layout')

@section('title')
<title>Welcome to openstackId - Server Admin - Edit API Endpoint</title>
@stop

@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<a href='{{  URL::action("AdminController@editApi", array("id"=>$endpoint->api_id)) }}'>Go Back</a>
<legend>{{ Lang::get("messages.edit_endpoint_title", array("id" => $endpoint->id)) }}</legend>
<div class="row-fluid">
    <div class="span6">
        <form class="form-horizontal" id="endpoint-form" name="endpoint-form" action='{{URL::action("ApiEndpointController@update",null)}}'>
            <fieldset>
                <div class="control-group">
                    <label  class="control-label" for="name">Name</label>
                    <div class="controls">
                        <input type="text" name="name" id="name" value="{{ $endpoint->name }}">
                    </div>
                </div>
                <div class="control-group">
                    <label  class="control-label" for="description">Description</label>
                    <div class="controls">
                        <textarea style="resize: none;" rows="4" cols="50" name="description" id="description">{{ $endpoint->description}}</textarea>
                    </div>
                </div>
                <div class="control-group">
                    <label  class="control-label" for="route">Route</label>
                    <div class="controls">
                        <input type="text" name="route" id="route" value="{{ $endpoint->route }}">
                    </div>
                </div>

                <div class="control-group">
                    <label  class="control-label" for="http_method">HTTP Method</label>
                    <div class="controls">
                       {{ Form::select('http_method', array('GET' => 'GET', 'POST' => 'POST', 'PUT' => 'PUT', 'DELETE' => 'DELETE'), $endpoint->http_method); }}
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <label class="checkbox">
                            <input type="checkbox" id="allow_cors"
                            @if ( $endpoint->allow_cors)
                            checked
                            @endif
                            name="allow_cors">&nbsp;allows CORS
                        </label>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <label class="checkbox">
                            <input type="checkbox" id="active"
                            @if ( $endpoint->active)
                            checked
                            @endif
                            name="active">&nbsp;Active
                        </label>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn">Save</button>
                    </div>
                </div>
                <input type="hidden" name="id" id="id" value="{{ $endpoint->id }}"/>
            </fieldset>
        </form>
    </div>
</div>

<div class="row-fluid">
    <div class="span6">
        <legend>{{Lang::get("messages.edit_endpoint_scope_title")}}&nbsp;<i class="icon-info-sign accordion-toggle" title='{{Lang::get("messages.edit_endpoint_scope_info_title")}}'></i></legend>
        <ul class="unstyled list-inline">
            @foreach($endpoint->api()->first()->scopes()->get() as $scope)
            {{-- scope header --}}
            <li>
                <label class="checkbox">
                    <input type="checkbox"
                           data-add-link='{{ URL::action("ApiEndpointController@addRequiredScope", array("id"=>$endpoint->id,"scope_id"=>$scope->id )) }}'
                           data-remove-link='{{ URL::action("ApiEndpointController@removeRequiredScope", array("id"=>$endpoint->id,"scope_id"=>$scope->id )) }}'
                           class="scope-checkbox" id="scope[]"
                    @if ( in_array($scope->id,$selected_scopes))
                    checked
                    @endif
                    value="{{$scope->id}}"/><span>{{trim($scope->name)}}</span>&nbsp;<i class="icon-info-sign accordion-toggle" title="{{$scope->description}}"></i>
                </label>
            </li>
            @endforeach
        </ul>
    </div>
</div>

@stop

@section('scripts')
<script type="application/javascript">
   var editEndpointMessages = {
	   success: '{{ Lang::get("messages.global_successfull_save_entity", array("entity" => "Endpoint")) }}'
   };
</script>
{{ HTML::script('js/oauth2/profile/admin/edit-endpoint.js') }}
@stop