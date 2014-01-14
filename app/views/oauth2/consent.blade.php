@extends('layout')
@section('title')
<title>Welcome to openstackId - Request for Permission </title>
@stop

@section('header_right')
    @if(Auth::check())
        <div class="row-fluid">
            <div class="span6 offset8">
                Welcome, <a href="{{ URL::action("UserController@getProfile") }}">{{Auth::user()->identifier}}</a>
            </div>
        </div>
    @endif
@stop

@section('content')
<div class="container">
    <div class="row-fluid">
        <div class="span6 offset3">
            <div class="row-fluid">
                <div class="span2">
                    <img src="{{$app_logo}}" border="0"/>
                </div>
                <div class="span10">
                    <h2>{{$app_name}}&nbsp;<i data-content="Developer Email: <a href='mailto:{{$dev_info_email}}'>{{$dev_info_email}}</a>.<br> Clicking 'Accept' will redirect you to: <a href='{{$redirect_to}}' target="_blank">{{$redirect_to}}</a>" class="icon-info-sign info" title="Developer Info"></i></h2>
                </div>
            </div>
            <legend>This app would like to:</legend>
            <ul class="unstyled list-inline">
            @foreach ($requested_scopes as $scope)
                <li> {{$scope->short_description}}&nbsp;<i class="icon-info-sign info" data-content="{{$scope->description}}" title="Scope Info"></i></li>
            @endforeach
            </ul>
            <p class="privacy-policy">
                ** <b>{{$app_name}}</b> Application and <b>Openstack</b> will use this information in accordance with their respective terms of service and privacy policies.
            </p>
            {{ Form::open(array('url' => '/accounts/user/consent','id'=>'authorization_form', 'method' => 'post',  "autocomplete" => "off")) }}
                <input type="hidden"  name='trust' id='trust' value=""/>
                <button class="btn" id="cancel-authorization" type="button">Cancel</button>
                <button class="btn btn-primary" id="approve-authorization" type="button">Accept</button>
            {{ Form::close() }}
        </div>
    </div>
</div>
@stop

@section('scripts')
<script type="application/javascript">
    $(document).ready(function() {

        $('.icon-info-sign').popover({html:true});

        $(':not(#anything)').on('click', function (e) {
            $('.icon-info-sign').each(function () {
                //the 'is' for buttons that trigger popups
                //the 'has' for icons and other elements within a button that triggers a popup
                if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                    $(this).popover('hide');
                    return;
                }
            });
        });

        $("body").on('click',"#cancel-authorization",function(event){
            $form = $('#authorization_form');
            $('#trust').attr('value','DenyOnce');
            $form.submit();
            event.preventDefault();
            return false;
        });

        $("body").on('click',"#approve-authorization",function(event){
            $form = $('#authorization_form');
            $('#trust').attr('value','AllowOnce');
            $form.submit();
            event.preventDefault();
            return false;
        });
    });
</script>
@stop