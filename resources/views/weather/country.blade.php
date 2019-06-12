@extends('layouts.base')

@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/105/three.min.js"></script>
<script src="{{ asset('js/common.js') }}" defer></script>
@endsection

@section('content')
<div class="my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('weather.region') }}">Top</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('weather.country', ['region'=>$region->id]) }}">{{$region->name}}</a>
            </li>
        </ol>
    </nav>
</div>

<h1 class="my-4">{{$region->name}} 地域の国</h1>

<div class="d-flex align-items-center my-4">
    <div>{{ $countries->links() }}</div>
    <div class="font-weight-bold mx-4">{{ $countries->total() }}件</div>
</div>

<div class="row">
    <div class="col-md-6 my-4">
        @foreach ($countries as $country)
        <div class="col-md-12">
            <a href="{{ route('weather.city', ['region'=>$country->region_id, 'country'=>$country->id]) }}">{{$country->name}}</a>
        </div>
        @endforeach
    </div>
</div>

<div id="earth-wrap" class="d-none">
    <canvas id="earth"></canvas>
</div>

@endsection