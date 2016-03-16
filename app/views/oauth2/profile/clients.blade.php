@extends('layout')
@section('title')
    <title>Welcome to OpenStackId - OAUTH2 Console - Clients</title>
@stop
@section('content')
    @include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
    <div class="row">
        <div id="clients" class="col-md-12">
            <legend><span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true"
                          title="Users can keep track of their registered applications and manage them"></span>&nbsp;Registered
                Applications
            </legend>
            {{ HTML::link(URL::action("ClientApiController@create",null),'Register Application',array('class'=>'btn btn-primary btn-md active add-client','title'=>'Adds a Registered Application')) }}
            @if (count($clients)>0)
                <table id='tclients' class="table table-hover table-condensed">
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Application Name</th>
                        <th>Application Type</th>
                        <th>Is Active</th>
                        <th>Is Locked</th>
                        <th>Modified</th>
                        <th>Modified By</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody id="body-registered-clients">
                    @foreach ($clients as $client)

                        <tr>
                            <td>@if (!$client->isOwner(Auth::user()))<i title="you have admin rights on this application" class="fa fa-user"></i>@endif</td>
                            <td>{{ $client->app_name }}</td>
                            <td>{{ $client->getFriendlyApplicationType()}}</td>
                            <td>
                                @if ($client->isOwner(Auth::user()))
                                <input type="checkbox" class="app-active-checkbox" id="app-active_{{$client->id}}"
                                       @if ( $client->active)
                                       checked
                                       @endif
                                       value="{{$client->id}}"/>
                                @endif
                            </td>
                            <td>
                                <input type="checkbox" class="app-locked-checkbox" id="app-locked_{{$client->id}}"
                                @if ( $client->locked)
                                       checked
                                       @endif
                                       value="{{$client->id}}" disabled="disabled" />
                            </td>
                            <td>{{ $client->updated_at }}</td>
                            <td>{{ $client->getEditedByNice() }}</td>
                            <td>&nbsp;
                                {{ HTML::link(URL::action("AdminController@editRegisteredClient",array("id"=>$client->id)),'Edit',array('class'=>'btn btn-default btn-md active edit-client','title'=>'Edits a Registered Application')) }}
                                @if ($client->canDelete(Auth::user()))
                                {{ HTML::link(URL::action("ClientApiController@delete",array("id"=>$client->id)),'Delete',array('class'=>'btn btn-default btn-md active del-client','title'=>'Deletes a Registered Application')) }}</td>
                                @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div id="dialog-form-application" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h3 id="myModalLabel">Register new Application</h3>
                </div>
                <div class="modal-body">
                    <p style="font-size: 10px;"><span class="glyphicon glyphicon-info-sign accordion-toggle" aria-hidden="true"
                                                   title="OAuth 2.0 allows users to share specific data with you (for example, contact lists) while keeping their usernames, passwords, and other information private."></span>
                        You need to register your application to get the necessary credentials to call a Openstack API
                    </p>
                    @include('oauth2.profile.add-client-form',array())
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button id='save-application' type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script type="application/javascript">
        var userId = {{$user_id}};
        var clientsUrls = {
            load: '{{ URL::action("ClientApiController@getByPage",array("offset"=>1,"limit"=>1000,"user_id"=>$user_id ))}}',
            edit: '{{ URL::action("AdminController@editRegisteredClient",array("id"=>"@id")) }}',
            delete: '{{ URL::action("ClientApiController@delete",array("id"=>"@id")) }}',
            add: '{{URL::action("ClientApiController@create",null)}}',
            activate: '{{ URL::action("ClientApiController@activate",array("id"=>"@id")) }}',
            deactivate: '{{ URL::action("ClientApiController@deactivate",array("id"=>"@id")) }}',
            fetchUsers: '{{URL::action("UserApiController@fetch",null)}}',
        };
    </script>
    {{ HTML::script('bower_assets/typeahead.js/dist/typeahead.bundle.js')}}
    {{ HTML::script('bower_assets/bootstrap-tagsinput/dist/bootstrap-tagsinput.js')}}
    {{ HTML::script('assets/js/oauth2/profile/clients.js') }}
@stop

@section('css')
    {{ HTML::style('bower_assets/bootstrap-tagsinput/dist/bootstrap-tagsinput.css') }}
    {{ HTML::style('bower_assets/bootstrap-tagsinput/dist/bootstrap-tagsinput-typeahead.css') }}
@append