@extends('layout')
@section('content')
<div class="container">
    {{ Form::open(array('url' => '/accounts/user/consent', 'method' => 'post')) }}
    <label>
        This Site {{ $realm }} is requesting permissions
    </label>
    {{ Form::submit('Ok') }}
    {{ Form::submit('Cancel') }}
    {{ Form::close() }}
</div>
@stop