@extends('layout')
@section('content')
<h1>OpenstackId Idp</h1>
<div class="span6" id="sidebar">
    <div class="row-fluid">
        <div class="span12">
            Hello, {{{ $username }}}.
            <a href="{{ URL::action("UserController@logout") }}"">logout</a>
            <div>Your OPENID: <a href="{{ str_replace("%23","#",$openid_url) }}">{{ str_replace("%23","#",$openid_url) }}</a></div>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            {{ Form::open(array('url' => URL::action('UserController@postUserProfileOptions'), 'method' => 'post')) }}
            <legend>Edit your profile options:</legend>
            <fieldset>
                <label class="checkbox">
                    {{ Form::checkbox('show_full_name', '1', $show_full_name) }}Show Full Name
                </label>
                <label class="checkbox">
                    {{ Form::checkbox('show_email', '1', $show_email) }}Show Email
                </label>
                <label class="checkbox">
                    {{ Form::checkbox('show_pic', '1', $show_pic) }}Show Photo
                </label>
                <div class="pull-right">
                    {{ Form::submit('Save',array('id'=>'save','class'=>'btn')) }}
                </div>
            </fieldset>
            {{ Form::close() }}
        </div>
    </div>
    @if (count($sites)>0)
    <div class="row-fluid">
        <div id="trusted_sites" class="span12">
            <h3>Trusted Sites</h3>
            <ul>
            @foreach ($sites as $site)
                <li><div><span>Realm {{ $site->getRealm() }} - Policy {{ $site->getAuthorizationPolicy() }}</span>&nbsp;{{ HTML::link(URL::action("UserController@get_deleteTrustedSite",array("id"=>$site->id)),'Delete',array('class'=>'btn del-realm')) }}</div></li>
            @endforeach
            </ul>
        </div>
    </div>
    @endif
</div>
<div class="span6">
</div>

