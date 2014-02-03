@extends('layout')

@section('title')
<title>Welcome to openstackId - Server Admin - Users</title>
@stop

@section('content')
@include('menu',array('is_oauth2_admin' => $is_oauth2_admin, 'is_openstackid_admin' => $is_openstackid_admin))
<legend>Locked Users</legend>
<div class="row-fluid">
    <div class="span12">
        <table id="users-table" class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody id="body-locked-users">
            @foreach($users as $user)
            <tr id="{{$user->id}}">
                <td>
                    <div style="min-width: 400px">
                        {{ $user->getFullName() }}
                    </div>
                </td>
                <td>
                    <div style="min-width: 100px">
                        {{ $user->getEmail() }}
                    </div>
                </td>
                <td>
                    {{ HTML::link(URL::action("UserApiController@unlock",array("id"=>$user->id)),'Unlock',array('data-user-id'=>$user->id,'class'=>'btn unlock-user','title'=>'Unlocks given user')) }}
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        <span id="users-info" class="label label-info">** There are not any locked Users.</span>
    </div>
</div>
@stop

@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {
        $('#server-admin','#main-menu').addClass('active');

        if($('#users-table tr').length===1){
            $('#users-info').show();
            $('#users-table').hide();
        }
        else{
            $('#users-info').hide();
            $('#users-table').show();
        }

        $("body").on('click',".unlock-user",function(event){
            if(confirm("Are you sure that you want to unlock this User?")){
                var url = $(this).attr('href');
                var user_id = $(this).attr('data-user-id');
                $.ajax(
                    {
                        type: "DELETE",
                        url: url,
                        dataType: "json",
                        timeout:60000,
                        success: function (data,textStatus,jqXHR) {
                            //load data...
                            $('#'+user_id,'#body-locked-users').remove();

                            if($('#users-table tr').length===1){
                                $('#users-info').show();
                                $('#users-table').hide();
                            }
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