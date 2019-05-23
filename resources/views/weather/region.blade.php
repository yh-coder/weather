@extends('layouts.base')

@section('content')
<div class="my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('weather.region') }}">Top</a>
            </li>
        </ol>
    </nav>
</div>

<h1 class="my-4">地域一覧</h1>

<div class="row">
    <div class="col-md-6 my-4">
        @foreach ($regions as $region)
        <div class="col-md-12">
            <a href="{{ route('weather.country', ['region'=>$region->id]) }}">{{$region->name}}</a>
        </div>
        @endforeach
    </div>
</div>

@endsection