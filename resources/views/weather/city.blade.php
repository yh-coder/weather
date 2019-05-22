@extends('layouts.base')

@section('content')
<div class="my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/">Top</a>
            </li>
            <li class="breadcrumb-item">
                <a href="/{{$region->id}}">{{$region->name}}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="/{{$country->region_id}}/{{$country->code}}">{{$country->name}}</a>
            </li>
        </ol>
    </nav>
</div>

<h1 class="my-4">{{$country->name}} 観測点 {{$cities->total()}}件</h1>

<div class="row my-4">
    <div id="weather-list" class="col-lg-6">
        @foreach ($cities as $city) @include('components/weather', ['title' => $city->name, 'data' => $city]) @endforeach
    </div>
    <div class="col-lg-6 d-none d-lg-block">
        <div class="maps-block">
            <div id="map-name" class="font-weight-bold my-3"></div>
            <iframe id="maps" src="" frameborder="0" style="width:100%; height:100%">map</iframe>
        </div>
    </div>
</div>

{{$cities->links()}}

@endsection