@extends('layout')

@section('title')
<title>Welcome to OpenStackId - Server Admin - Resource Server</title>
@stop

@section('css')

@append

@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<div class="row">
    <div class="row">
        <h4 style="float:left"><span aria-hidden="true" class="glyphicon glyphicon-info-sign pointable" title="Registered Resource Servers"></span>&nbsp;Resource Servers</h4>
        <div style="position: relative;float:left;">
            <div style="position:absolute;top:13px;margin-left:5px"><span aria-hidden="true" class="glyphicon glyphicon-refresh pointable refresh-servers"title="Update Resource Server List"></span></div>
        </div>
    </div>
    <div class="row">
        <div class="alert alert-info" id="info-resource-servers" style="display: none">
            <strong>There are not any available Resource Servers</strong>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            {!! HTML::link(URL::action("Api\ApiResourceServerController@create"),'Add Resource Server',array('class'=>'btn active btn-primary add-resource-server','title'=>'Adds a New Resource Server')) !!}
        </div>
    </div>
    <table id='table-resource-servers' class="table table-hover table-condensed">
        <thead>
        <tr>
            <th>Friendly Name</th>
            <th>Host</th>
            <th>IP Addresses</th>
            <th>Active</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody id="body-resource-servers">
        @foreach ($resource_servers as $resource_server)
        <tr id="{!! $resource_server->id !!}">
            <td width="25%">{!!$resource_server->friendly_name!!}</td>
            <td width="25%">{!!$resource_server->host!!}</td>
            <td width="10%">{!!$resource_server->ips!!}</td>
            <td width="5%">
                <input type="checkbox" class="resource-server-active-checkbox" id="resource-server-active_{!!$resource_server->id!!}"
                       data-resource-server-id="{!!$resource_server->id!!}"
                @if ( $resource_server->active)
                checked
                @endif
                value="{!!$resource_server->id!!}"/>
            </td>
            <td width="25%">
                &nbsp;
                {!! HTML::link(URL::action("AdminController@editResourceServer",array("id"=>$resource_server->id)),'Edit',array('class'=>'btn btn-default active edit-resource-server','title'=>'Edits a Registered Resource Server')) !!}
                {!! HTML::link(URL::action("Api\ApiResourceServerController@delete",array("id"=>$resource_server->id)),'Delete',array('class'=>'btn btn-default btn-delete active delete-resource-server','title'=>'Deletes a Registered Resource Server')) !!}
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

@include('modal', array ('modal_id' => 'dialog-form-resource-server', 'modal_title' => 'Register New Resource Server', 'modal_save_css_class' => 'save-resource-server', 'modal_save_text' => 'Save', 'modal_form' => 'oauth2.profile.admin.resource-server-add-form', 'modal_form_data' => array()))

@stop

@section('scripts')
<script type="application/javascript">
	var resourceServerUrls = {
		get : '{!!URL::action("Api\ApiResourceServerController@getByPage",array("offset"=>1,"limit"=>1000))!!}',
		edit : '{!! URL::action("AdminController@editResourceServer",array("id"=>-1)) !!}',
		delete : '{!! URL::action("Api\ApiResourceServerController@delete",array("id"=>-1)) !!}',
		activate : '{!! URL::action("Api\ApiResourceServerController@activate",array("id"=>"@id")) !!}',
		deactivate : '{!! URL::action("Api\ApiResourceServerController@deactivate",array("id"=>"@id")) !!}',
		add : '{!!URL::action("Api\ApiResourceServerController@create",null)!!}'
	};
</script>

{!! HTML::script('assets/js/oauth2/profile/admin/resource-servers.js') !!}
@append