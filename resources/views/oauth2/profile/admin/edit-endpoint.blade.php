@extends('layout')

@section('title')
    <title>Welcome to OpenStackId - Server Admin - Edit API Endpoint</title>
@stop

@section('content')
    @include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
    <a href='{{  URL::action("AdminController@editApi", array("id"=>$endpoint->api_id)) }}'>Go Back</a>
    <legend>{{ Lang::get("messages.edit_endpoint_title", array("id" => $endpoint->id)) }}</legend>
    <div class="row-fluid">
        <div class="span6">
            <form id="endpoint-form" name="endpoint-form" action='{{URL::action("Api\ApiEndpointController@update",null)}}'>
                <div class="form-group">
                    <label for="name">Name</label>
                    <input class="form-control" type="text" name="name" id="name" value="{{ $endpoint->name }}">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" style="resize: none;" rows="4" cols="50" name="description"
                              id="description">{{ $endpoint->description}}</textarea>
                </div>
                <div class="form-group">
                    <label for="route">Route&nbsp;<span aria-hidden="true"
                                                        class="glyphicon glyphicon-info-sign pointable"
                                                        title=''></span></label>
                    <input  class="form-control" type="text" name="route" id="route" value="{{ $endpoint->route }}">
                </div>

                <div class="form-group">
                    <label for="rate_limit">Rate Limit (Per Hour)&nbsp;<span aria-hidden="true"
                                                                       class="glyphicon glyphicon-info-sign pointable"
                                                                       title=''></span></label>
                    <input class="form-control" type="number" name="rate_limit" id="rate_limit" value="{{ $endpoint->rate_limit }}">
                </div>
                <div class="form-group">
                    <label for="http_method">HTTP Method&nbsp;<span aria-hidden="true"
                                                                    class="glyphicon glyphicon-info-sign pointable"
                                                                    title=''></span></label>
                    {{ Form::select('http_method', array('GET' => 'GET', 'POST' => 'POST', 'PUT' => 'PUT', 'DELETE' => 'DELETE'), $endpoint->http_method,array('class' => 'form-control', 'id' => 'http_method')); }}
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="allow_cors"
                               @if ( $endpoint->allow_cors)
                               checked
                               @endif
                               name="allow_cors">&nbsp;allows CORS&nbsp;<span aria-hidden="true"
                                                                              class="glyphicon glyphicon-info-sign pointable"
                                                                              title=''></span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="active"
                               @if ( $endpoint->active)
                               checked
                               @endif
                               name="active">&nbsp;Active
                    </label>
                </div>

                <button type="submit" class="btn btn-default active">Save</button>
                <input type="hidden" name="id" id="id" value="{{ $endpoint->id }}"/>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <legend>{{Lang::get("messages.edit_endpoint_scope_title")}}&nbsp;<span aria-hidden="true"
                                                                                   class="glyphicon glyphicon-info-sign pointable"
                                                                                   title='{{Lang::get("messages.edit_endpoint_scope_info_title")}}'></span>
            </legend>
            <ul class="unstyled list-inline">
                @foreach($endpoint->api()->first()->scopes()->get() as $scope)
                    {{-- scope header --}}
                    <li>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"
                                       data-add-link='{{ URL::action("Api\ApiEndpointController@addRequiredScope", array("id"=>$endpoint->id,"scope_id"=>$scope->id )) }}'
                                       data-remove-link='{{ URL::action("Api\ApiEndpointController@removeRequiredScope", array("id"=>$endpoint->id,"scope_id"=>$scope->id )) }}'
                                       class="scope-checkbox" id="scope[]"
                                       @if ( in_array($scope->id,$selected_scopes))
                                       checked
                                       @endif
                                       value="{{$scope->id}}"/><span>{{trim($scope->name)}}</span>&nbsp;<span
                                        aria-hidden="true" class="glyphicon glyphicon-info-sign pointable"
                                        title="{{$scope->description}}"></span>
                            </label>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

@stop

@section('scripts')
    <script type="application/javascript">
        var editEndpointMessages = {
            success: '{{ Lang::get("messages.global_successfully_save_entity", array("entity" => "Endpoint")) }}'
        };
    </script>
    {{ HTML::script('assets/js/oauth2/profile/admin/edit-endpoint.js') }}
@append