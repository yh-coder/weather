<?php

namespace App\Services;

use DB;

use App\Model\Region;
use App\Model\Country;
use App\Model\City;
use App\Model\Weather;

use \Datetime;

class WeatherService
{
    /**
     * Undocumented function
     */
    public function __construct()
    {
    }


    /**
     * Undocumented function
     *
     * @param int|null $id
     * @return Region
     */
    public function getRegion($id = null)
    {
        if (is_null($id)) {
            return Region::all();
        } else {
            return Region::where('id', '=', $id)->get();
        }
    }

    /**
     * Undocumented function
     *
     * @param int $region_id
     * @return void
     */
    public function getCountries($region_id)
    {
        return Country::where('region_id', '=', $region_id)
            ->orderBy('name')
            ->paginate(20);
    }

    /**
     * Undocumented function
     *
     * @param string $country_code
     * @return void
     */
    public function getCountry($country_code)
    {
        return Country::where('code', '=', $country_code)->get();
    }

    /**
     * Undocumented function
     *
     * @param string $country_code
     * @return void
     */
    public function getCity($country_code)
    {
        // 空または24時間経過した気象情報のフラグ
        $case_sql = <<<CASE
case
    when weather.updated_at is null then 1
    when date_add(weather.updated_at, interval 24 hour) < now() then 1
    else 0
end as flag
CASE;

        // 観測点と天気の情報をDBから取得
        $case = DB::raw($case_sql);
        $list = DB::table('city')
            ->leftJoin('weather', 'city.id', '=', 'weather.id')
            ->where('country_code', '=', $country_code)
            ->orderBy('name')
            ->select(['city.id AS city_id', 'city.*', 'weather.*', $case])
            ->paginate(20);
        
        // 更新が必要なIDを抽出
        $id_map = [];
        foreach ($list as $i => &$cw) {
            // id で上書き対策
            $cw->id = $cw->city_id;
            if ($cw->flag===1) {
                $id_map[$cw->id] = $i;
            }
        }

        // 更新が必要なIDがあれば取得
        if (!empty($id_map)) {
            $weathers = $this->getWeather($id_map);
            foreach ($weathers as $i => $w) {
                $list[$i]->weather_text = $w->weather_text;
                $list[$i]->weather_icon = $w->weather_icon;
                $list[$i]->temperature = $w->temperature;
                $list[$i]->temperature_max = $w->temperature_max;
                $list[$i]->temperature_min = $w->temperature_min;
                $list[$i]->wind_speed = $w->wind_speed;
                $list[$i]->wind_degree = $w->wind_degree;
                $list[$i]->created_at = $w->created_at;
                $list[$i]->updated_at = $w->updated_at;
                $list[$i]->flag = 0;
            }
        }

        return $list;
    }

    /**
     * 指定したIDの気象情報をAPIから取得
     *
     * @param array $id_map
     * @return Weathers[]
     */
    public function getWeather($id_map)
    {
        $app_key = env('WEATHER_API_KEY');
        $url = "http://api.openweathermap.org/data/2.5/group?APPID=${app_key}&units=metric&id=".join(',', array_keys($id_map));

        $json = json_decode(file_get_contents($url), true);
        $res = [];
        foreach ($json['list'] as $data) {
            $w = Weather::firstOrNew(['id' => $data['id']]);
            $w->weather_text = $data['weather'][0]['main'];
            $w->weather_icon = $data['weather'][0]['icon'];
            $w->temperature = $data['main']['temp'];
            $w->temperature_max = $data['main']['temp_max'];
            $w->temperature_min = $data['main']['temp_min'];
            $w->wind_speed =  $data['wind']['speed'];
            $w->wind_degree = $data['wind']['deg'];
            $w->created_at = new Datetime();
            $w->updated_at = new Datetime();
            $w->save();

            $res[$id_map[$data['id']]] = $w;
        }
        return $res;
    }
}
