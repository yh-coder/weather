<div class="city-info border-bottom px-3 py-3 {{ $data->status<2?'bg-inactive':''}}" data-name="{{$data->name}}" data-lat="{{ $data->coord_lat }}" data-lon="{{$data->coord_lon}}">
    <div class="row">
        <div class="col-9 font-weight-bold"><h4>{{ $data->name }}</h4></div>
        <div class="col-3 text-right">
            <button class="btn btn-sm btn-primary getmaps py-0 d-none d-lg-inline">
                <i class="fas fa-globe"></i>
                地図
            </button>
            @php
                if ($data->status==0) {
                    $info = "最新の気象データの取得に失敗しました。<br>時間をおいて再度アクセスしてください。";
                } elseif ($data->status==1) {
                    $info = $data->updated_at." 取得<br>最新の気象データの取得に失敗しました。<br>時間をおいて再度アクセスしてください。";
                } else {
                    $info = $data->updated_at." 取得";
                }
            @endphp
            <button type="button" class="btn btn-sm btn-secondary py-0" data-container="body" data-html="true" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="{{ $info }}">
                info
            </button>
        </div>
    </div>
    @if($data->status > 0)
    <div class="row align-items-center">
        <div class="col-2 font-weight-bold">天気</div>
        <div class="col-2">
            <img src="http://openweathermap.org/img/w/{{$data->weather_icon}}.png" alt="{{$data->weather_text}}">
        </div>
        <div class="col-2 font-weight-bold">風速</div>
        <div class="col-2">
            {{ $data->wind_speed }} m/s
        </div>
        <div class="col-2 font-weight-bold">風向き</div>
        <div class="col-2">
            @if(!is_null($data->wind_degree))
                <i class="fas fa-arrow-alt-circle-up wind-icon mx-3" style="transform: rotate({{ $data->wind_degree }}deg);"></i>
            @endif
        </div>
    </div>
    <div class="row align-items-center">
        <div class="col-2 font-weight-bold">気温</div>
        <div class="col-2">{{$data->temperature}}℃</div>
        <div class="col-2 font-weight-bold">最低気温</div>
        <div class="col-2">{{ $data->temperature_min }}℃</div>
        <div class="col-2 font-weight-bold">最高気温</div>
        <div class="col-2">{{ $data->temperature_max }}℃</div>
    </div>
    @endif
</div>