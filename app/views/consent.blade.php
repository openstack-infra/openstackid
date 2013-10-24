@extends('layout')
@section('content')
<div class="container">
    {{ Form::open(array('url' => '/accounts/user/consent', 'method' => 'post',  "autocomplete" => "off")) }}
    <label>
        This Site {{ $realm }} is requesting permissions
    </label>

    <div>
        {{ Form::label("allow_forever","Allow Forever")}}
        {{ Form::radio('trust[]', 'AllowForever','true',array('id'=>'allow_forever')) }}
        {{ Form::label("allow_once","Allow Once")}}
        {{ Form::radio('trust[]', 'AllowOnce','',array('id'=>'allow_once')) }}
        {{ Form::label("deny_once","Deny Once")}}
        {{ Form::radio('trust[]', 'DenyOnce','',array('id'=>'deny_once')) }}
        {{ Form::label("deny_forever","Deny Forever")}}
        {{ Form::radio('trust[]', 'DenyForever','',array('id'=>'deny_forever')) }}
    </div>
    {{ Form::submit('Ok') }}
    {{ Form::submit('Cancel') }}
    {{ Form::close() }}
    @foreach ($views as $view)
    {{ $view}}
    @endforeach
</div>
@stop