
<div class="row align-items-center">
    <div class="col">{{ $data->forecast_at->format('Y/m/d H') }}時の天気</div>
</div>
<div class="row align-items-center">
    <div class="col-2 font-weight-bold">天気</div>
    <div class="col-2">
        <img src="https://openweathermap.org/img/w/{{$data->weather_icon}}.png" alt="{{$data->weather_text}}">
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