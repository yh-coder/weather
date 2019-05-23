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
            <li class="breadcrumb-item">
                <a href="{{ route('weather.city', ['region'=>$country->region_id, 'country'=>$country->id, 'city'=>$city->id]) }}">{{$city->name}}</a>
            </li>
        </ol>
    </nav>
</div>

<div class="row my-4">
    <div class="col-lg-12">
        <h1>{{$city->name}} 天気予報</h1>
    </div>
</div>

<div class="row my-4">
    <div id="weather-list" class="col-lg-6">
        <div class="city-info px-3 py-3 {{ $forecast['status']<2?'bg-inactive':''}}" data-name="{{$city->name}}" data-lat="{{ $city->coord_lat }}" data-lon="{{$city->coord_lon}}">
            <div class="row">
                <div class="col-9 font-weight-bold"><h4>{{ $city->name }}</h4></div>
                <div class="col-3 text-right">
                    <button class="btn btn-sm btn-primary getmaps py-0 d-none d-lg-inline">
                        <i class="fas fa-globe"></i>
                        地図
                    </button>
                    @php
                        if ($forecast['status']==0) {
                            $info = "最新の気象データの取得に失敗しました。<br>時間をおいて再度アクセスしてください。";
                        } elseif ($forecast['status']==1) {
                            $info = $forecast['list'][0]->updated_at." 取得<br>最新の気象データの取得に失敗しました。<br>時間をおいて再度アクセスしてください。";
                        } else {
                            $info = $forecast['list'][0]->updated_at." 取得";
                        }
                    @endphp
                    <button type="button" class="btn btn-sm btn-secondary py-0" data-container="body" data-html="true" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="{{ $info }}">
                        info
                    </button>
                </div>
            </div>
            @foreach ($forecast['list'] as $data)
            <div class="border-bottom py-2">
                @include('components/weather', ['data' => $data])
            </div>
            @endforeach
        </div>
    </div>
    <div class="col-lg-6 d-none d-lg-block">
        <div class="maps-block">
            <div id="map-name" class="font-weight-bold my-3"></div>
            <iframe id="maps" src="" frameborder="0" style="width:100%; height:100%">map</iframe>
        </div>
    </div>
</div>

@endsection