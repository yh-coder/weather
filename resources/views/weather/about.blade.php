@extends('layouts.base')

@section('content')
<div class="my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/">Top</a>
            </li>
        </ol>
    </nav>
</div>

<div class="jumbotron">
    <h1 class="display-6">About</h1>
    <hr class="my-4">
    このサービスでは地域の気象情報を掲載しています。<br>
    地域の区分は国連による世界地理区分(m49)に基づきます。<br>
    <a target="_blank" href="https://unstats.un.org/unsd/methodology/m49/overview">https://unstats.un.org/unsd/methodology/m49/overview</a><br>
    国名コードの標準はISO3166に基づきます。<br>
    <a target="_blank" href="https://www.iso.org/iso-3166-country-codes.html">https://www.iso.org/iso-3166-country-codes.html</a><br>
    気象情報はOpenWeatherMapから取得しています。<br>
    <a target="_blank" href="https://openweathermap.org/">https://openweathermap.org/</a><br>
    <p class="lead float-right">
        <a class="btn btn-primary" href="{{route('weather.region')}}" role="button">Topへ</a>
    </p>
</div>

@endsection