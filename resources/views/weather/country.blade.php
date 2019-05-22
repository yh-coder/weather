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
        </ol>
    </nav>
</div>

<h1 class="my-4">{{$region->name}} 地域の国 {{$countries->total()}}件</h1>

<div class="row">
    <div class="col-md-6 my-4">
        @foreach ($countries as $country)
        <div class="col-md-12">
            <a href="/{{$country->region_id}}/{{$country->code}}">{{$country->name}}</a>
        </div>
        @endforeach
    </div>
</div>

{{$countries->links()}}

@endsection