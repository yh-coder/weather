<!DOCTYPE html>
<html lang="{{ app()->getlocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ env('APP_NAME') }}</title>
        
        @yield('javascript')
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" crossorigin="anonymous">
    </head>
    <body>
        <header>
            <nav class="navbar navbar-expand-lg navbar-laravel">
                <div class="container">
                    <span class="navbar-brend">{{ env('APP_NAME') }}</span>
                    <div class="float-lg-right">
                        <a class="btn btn-sm btn-secondary" href="{{route('weather.about')}}">About</a>
                    </div>
                </div>
            </nav>
        </header>
        <div id="app" class="container">
            @yield('content')
        </div>
    </body>
</html>