@extends('layouts.base')

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
    <div class="font-weight-bold mx-4">{{ $countries->total() }}件該当</div>
    <div>{{ $countries->links() }}</div>
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

@endsection