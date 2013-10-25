<!DOCTYPE html>
<html>
<head>
    <title>OpenstackId Idp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{ HTML::style('css/bootstrap.css') }}
    {{ HTML::script('js/jquery-2.0.3.min.js')}}
</head>
<body>
    <div
    @yield('content')

    {{ HTML::script('js/bootstrap.min.js')}}
    @yield('scripts')
</body>

</html>