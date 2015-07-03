<!DOCTYPE html>
<html lang="en">
<head>
    @yield('title')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{ HTML::style('bower_assets/bootstrap/dist/css/bootstrap.min.css') }}
    {{ HTML::style('assets/css/main.css') }}
    {{ HTML::style('bower_assets/jquery-ui/themes/ui-darkness/jquery-ui.css') }}
    {{ HTML::style('bower_assets/fontawesome/css/font-awesome.min.css') }}
    @yield('css')
</head>
<body>
    <div class="container">
        <header class="row header">
            <div class="col-md-5">
                <h1 id="logo"><a href="/">Open Stack</a></h1>
            </div>
            <div class="col-md-7">
                @yield('header_right')
            </div>
        </header>
        <div class="row" id="main-content">
            @yield('content')
        </div>
        <footer class="row"></footer>
    </div>
    {{ HTML::script('bower_assets/jquery/dist/jquery.min.js')}}
    {{ HTML::script('bower_assets/bootstrap/dist/js/bootstrap.min.js')}}
    {{ HTML::script('bower_assets/jquery-validate/dist/jquery.validate.min.js')}}
    {{ HTML::script('bower_assets/jquery-validate/dist/additional-methods.min.js')}}
    {{ HTML::script('bower_assets/pure-templates/libs/pure.min.js')}}
    {{ HTML::script('bower_assets/uri.js/src/URI.min.js')}}
    {{ HTML::script('assets/js/ajax.utils.js')}}
    {{ HTML::script('assets/js/jquery.cleanform.js')}}
    {{ HTML::script('assets/js/jquery.serialize.js')}}
    {{ HTML::script('assets/js/jquery.validate.additional.custom.methods.js')}}
    @yield('scripts')
</body>
</html>