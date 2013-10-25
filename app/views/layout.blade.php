<!DOCTYPE html>
<html>
<head>
    <title>OpenstackId Idp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{ HTML::style('css/bootstrap.css') }}

</head>
<body>
    <div class="container">
        <div class="row" id="main-content">
            @yield('content')
        </div>
        <footer class="row"></footer>
    </div>
    {{ HTML::script('js/jquery-2.0.3.min.js')}}
    {{ HTML::script('js/bootstrap.min.js')}}
    @yield('scripts')
</body>

</html>