@extends('layout')
@section('content')
    <h1>OpenStackId Idp - 400</h1>
    <div class="container">
        <p>
            400. That’s an error.
        </p>
        <p>
            <b>{{ $error_code }}</b>
        </p>
        <p>
            {{ $error_description }}
        </p>
    </div>
@stop