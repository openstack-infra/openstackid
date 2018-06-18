<!DOCTYPE html>
<html lang="en">
<head>
    @yield('title')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" href="/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/favicon/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/favicon/manifest.json">
    <link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#5bbad5">
    @yield('meta')
    {!! HTML::style('assets/css/index.css') !!}
    {!! HTML::style('assets/css/main.css') !!}
    {!! HTML::style('assets/bootstrap-tagsinput/bootstrap-tagsinput-typeahead.css') !!}
    {!! HTML::style('assets/bootstrap-tagsinput/bootstrap-tagsinput.css') !!}
    {!! HTML::style('assets/sweetalert2/sweetalert2.css') !!}
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
    {!! HTML::script('assets/__common__.js')!!}
    {!! HTML::script('assets/index.js')!!}
    {!! HTML::script('assets/js/ajax.utils.js')!!}
    {!! HTML::script('assets/js/jquery.cleanform.js')!!}
    {!! HTML::script('assets/js/jquery.serialize.js')!!}
    {!! HTML::script('assets/js/jquery.validate.additional.custom.methods.js')!!}
    {!! HTML::script('assets/typeahead/typeahead.bundle.js')!!}
    {!! HTML::script('assets/bootstrap-tagsinput/bootstrap-tagsinput.js')!!}
    {!! HTML::script('assets/sweetalert2/sweetalert2.js')!!}
    {!! HTML::script('assets/urijs/URI.min.js')!!}
    @yield('scripts')
    <span class="version hidden">{!! Config::get('app.version') !!}</span>
</body>
</html>