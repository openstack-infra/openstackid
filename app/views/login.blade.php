@extends('layout')
@section('content')
<h1>Login</h1>
<div class="container">
    {{ Form::open(array('url' => '/accounts/user/login', 'method' => 'post')) }}
        <ul class="errors">
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
        <!-- username field -->
        <div>
            {{ Form::label('username', 'user') }}
            {{ Form::text('username') }}
        </div>
        <div>
            {{ Form::label('password', 'password') }}
            {{ Form::password('password') }}
        </div>
        <div>
            {{ Form::label('remember', 'remember me') }}
            {{ Form::checkbox('remember', '1', false) }}
        </div>
        {{ Form::submit('Login') }}
        @if(Session::has('flash_notice'))
            <div id="flash_notice">{{ Session::get('flash_notice')  }}</div>
        @endif
    {{ Form::close() }}
</div>
@stop