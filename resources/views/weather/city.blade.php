<!DOCTYPE html>
<html lang="{{ app()->getlocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
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

    <div class="container">
        <div class="my-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Top</a></li>
                    <li class="breadcrumb-item"><a href="/{{$region->id}}">{{$region->name}}</a></li>
                    <li class="breadcrumb-item"><a href="/{{$country->region_id}}/{{$country->code}}">{{$country->name}}</a></li>
                </ol>
            </nav>
        </div>
        
        <h1 class="my-4">{{$country->name}} 観測点一覧</h1>

        {{$cities->links()}}

        <div class="my-4">
            @foreach ($cities as $city)
            <div class="col-md-12">{{$city->name}}</a></div>
            @endforeach
        </div>
    </div>
    
</body>
</html>