@extends('layouts.base')

@section('content')

<div class="my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('weather.region') }}">Top</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('weather.country', ['region'=>$country->region_id]) }}">{{$region->name}}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('weather.city', ['region'=>$country->region_id, 'country'=>$country->id]) }}">{{$country->name}}</a>
            </li>
        </ol>
    </nav>
</div>

<div class="d-flex d-flex justify-content-between">
    <h1>{{$country->name}} 現在の天気</h1>
    <div class="city-top">
        <form action="{{ route('weather.city', ['region'=>$country->region_id, 'country'=>$country->id]) }}" method="get">
            <div class="d-flex justify-content-end">
                <input type="text" class="form-control form-control-sm" name="q" value="{{ $find_name }}" placeholder="観測点名">
                <button tyep="submit" class="btn btn-sm btn-primary text-nowrap">検索</button>
            </div>
        </form>
    </div>
</div>

<div class="d-flex align-items-center my-4">
    <div>{{ $cities->appends(['q'=>$find_name])->links() }}</div>
    <div class="font-weight-bold mx-4">{{ $cities->total() }}件</div>
</div>

<div class="row my-4">
    <div id="weather-list" class="col-lg-6">
        @foreach ($cities as $city)
        <div class="city-info border-bottom px-3 py-3 {{ $city->status<2?'bg-inactive':''}}" data-name="{{$city->name}}" data-lat="{{ $city->coord_lat }}" data-lon="{{$city->coord_lon}}">
            <div class="row">
                <div class="col-9 font-weight-bold"><h4><a href="{{ route('weather.forecast', ['region'=>$country->region_id, 'country'=>$country->id, 'city'=>$city->id]) }}">{{ $city->name }}</a></h4></div>
                <div class="col-3 text-right">
                    <button class="btn btn-sm btn-primary getmaps py-0 d-none d-lg-inline">
                        <i class="fas fa-globe"></i>
                        地図
                    </button>
                    @php
                        if ($city->status==0) {
                            $info = "最新の気象データの取得に失敗しました。<br>時間をおいて再度アクセスしてください。";
                        } elseif ($city->status==1) {
                            $info = $city->updated_at." 取得<br>最新の気象データの取得に失敗しました。<br>時間をおいて再度アクセスしてください。";
                        } else {
                            $info = $city->updated_at." 取得";
                        }
                    @endphp
                    <button type="button" class="btn btn-sm btn-secondary py-0" data-container="body" data-html="true" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="{{ $info }}">
                        info
                    </button>
                </div>
            </div>
            @if($city->status > 0)
            @include('components/weather', ['data' => $city])
            @endif
        </div>
        @endforeach
    </div>
    <div class="col-lg-6 d-none d-lg-block">
        <div class="maps-block">
            <div id="map-name" class="font-weight-bold my-3"></div>
            <iframe id="maps" src="" frameborder="0" style="width:100%; height:100%">map</iframe>
        </div>
    </div>
</div>

@endsection