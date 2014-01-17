<!DOCTYPE html>
<html>
<head>
    @yield('title')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{ HTML::style('css/bootstrap.min.css') }}
    {{ HTML::style('css/main.css') }}
    {{ HTML::style('css/smoothness/jquery-ui-1.10.3.custom.min.css') }}
</head>
<body>
    <div class="container">
        <header class="row">
            <div class="span5">
                <h1 id="logo"><a href="/">Open Stack</a></h1>
            </div>
            <div class="span7">
                @yield('header_right')
            </div>
        </header>
        <div class="row" id="main-content">
            @yield('content')
        </div>
        <footer class="row"></footer>
    </div>
    {{ HTML::script('js/jquery-2.0.3.min.js')}}
    {{ HTML::script('js/bootstrap.min.js')}}
    {{ HTML::script('js/pure.min.js')}}
    {{ HTML::script('js/jquery-ui-1.10.3.custom.min.js')}}
    {{ HTML::script('js/jquery.validate.min.js')}}
    {{ HTML::script('js/additional-methods.min.js')}}
    @yield('scripts')
    @yield('subscripts')
</body>

</html>