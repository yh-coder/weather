<!DOCTYPE html>
<html lang="{{ app()->getlocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ env('APP_NAME') }}</title>

    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-laravel">
            <span class="navbar-brend">{{ env('APP_NAME') }}</span>
        </nav>
    </div>

    <div id="app" class="container">
        <div class="my-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Top</a></li>
                </ol>
            </nav>
        </div>

        <h1 class="my-4">地域一覧</h1>

        <div class="row">
            <div class="col-md-6 my-4">
                @foreach ($regions as $region)
                <div class="col-md-12"><a href="{{$region->id}}">{{$region->name}}</a></div>
                @endforeach
            </div>
        </div>
    </div>
    
</body>
</html>