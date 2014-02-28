@extends('layout')
@section('title')
<title>Welcome to openstackId - Server Admin - Edit API Scope</title>
@stop
@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<a href='{{  URL::action("AdminController@editApi",array("id"=>$scope->api_id)) }}'>Go Back</a>
<legend>Edit API Scope - Id {{ $scope->id }}</legend>
<div class="row-fluid">
    <div class="span6">
        <form class="form-horizontal" id="scope-form" name="scope-form" action='{{URL::action("ApiScopeController@update",null)}}'>
            <fieldset>
                <div class="control-group">
                    <label  class="control-label" for="name">Name</label>
                    <div class="controls">
                        <input type="text" name="name" id="name" value="{{ $scope->name }}">
                    </div>
                </div>
                <div class="control-group">
                    <label  class="control-label" for="description">Description</label>
                    <div class="controls">
                        <textarea style="resize: none;" rows="4" cols="50" name="description" id="description">{{ $scope->description}}</textarea>
                    </div>
                </div>
                <div class="control-group">
                    <label  class="control-label" for="short_description">Short Description</label>
                    <div class="controls">
                        <textarea style="resize: none;" rows="4" cols="50" name="short_description" id="short_description">{{ $scope->short_description}}</textarea>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" id="default"
                        @if ( $scope->default)
                        checked
                        @endif
                        name="default">&nbsp;Default
                    </label>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" id="system"
                        @if ( $scope->system)
                        checked
                        @endif
                        name="system">&nbsp;System
                    </label>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <label class="checkbox">
                            <input type="checkbox" id="active"
                            @if ( $scope->active)
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
                <input type="hidden" name="id" id="id" value="{{ $scope->id }}"/>
            </fieldset>
        </form>
    </div>
</div>
@stop

@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {

        $('#server-admin','#main-menu').addClass('active');

        var scope_form = $('#scope-form');

        var scope_validator = scope_form.validate({
            rules: {
                "name"  :              {required: true, scopename:true,rangelength: [1, 512]},
                "short_description":   {required: true, free_text:true,rangelength: [1, 512]},
                "description":         {required: true, free_text:true,rangelength: [1, 1024]}
            }
        });

        scope_form.submit(function( event ) {
            var is_valid = scope_form.valid();
            if (is_valid){
                scope_validator.resetForm();
                var scope = scope_form.serializeForm();
                var href = $(this).attr('action');
                $.ajax(
                    {
                        type: "PUT",
                        url: href,
                        data: JSON.stringify(scope),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            displaySuccessMessage('{{ Lang::get("messages.global_successfully_save_entity", array("entity" => "Scope")) }}',scope_form);
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
