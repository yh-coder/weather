<?php

namespace App\Services;

use DB;

use App\Model\Region;
use App\Model\Country;
use App\Model\City;
use App\Model\Weather;

use \Datetime;
use \Exception;

class WeatherService
{
    /**
     * Undocumented function
     */
    public function __construct()
    {
    }


    /**
     * 地域を列挙
     *
     * @return Region[]
     */
    public function getRegions()
    {
        return Region::all();
    }

    
    /**
     * idを指定して地域を取得
     *
     * @param int $id
     * @return Region
     */
    public function getRegion($id)
    {
        return Region::find($id);
    }

    /**
     * 地域を指定し国を取得
     *
     * @param int $page_size;
     * @param int $region_id
     * @return Country[]
     */
    public function getCountries($page_size, $region_id)
    {
        return Country::where('region_id', '=', $region_id)
            ->orderBy('name')
            ->paginate($page_size);
    }
    
    /**
     * 地域とコードを指定し国を取得
     *
     * @param string $country_code
     * @return Country
     */
    public function getCountry($region_id, $country_code)
    {
        return Country::where('region_id', '=', $region_id)
            ->where('code', '=', $country_code)
            ->first();
    }

    /**
     * 観測点と気象情報を返す
     * 更新に失敗したデータはstatusが2以外
     *
     * @param int $page_size
     * @param string $country_code
     * @return void
     */
    public function getCityWeather($page_size, $country_code)
    {
        // DBにある気象情報のステータス
        // 0: 該当なし、1: 更新から24時間経過、2:更新から24時間以内
        $case_sql = <<<CASE
case
    when weather.updated_at is null then 0
    when date_add(weather.updated_at, interval 24 hour) < now() then 1
    else 2
end as status
CASE;

        $sql = DB::raw($case_sql);
        // 観測点と天気の情報をDBから取得
        $list = DB::table('city')
            ->leftJoin('weather', 'city.id', '=', 'weather.id')
            ->where('country_code', '=', $country_code)
            ->orderBy('name')
            ->select(['city.id AS city_id', 'city.*', 'weather.*', $sql])
            ->paginate($page_size);
        
        // 更新が必要なIDを抽出
        $id_map = [];
        foreach ($list as $i => &$cw) {
            // id で上書き対策
            $cw->id = $cw->city_id;
            if ($cw->status!==2) {
                $id_map[$i] = $cw->id;
            }
        }

        // 更新が必要なIDがあれば取得しステータス更新
        if (!empty($id_map)) {
            $weathers = $this->getWeather($id_map);
            // kv入れ替え
            $id_map = array_flip($id_map);
            foreach ($weathers as $id => $w) {
                if (array_key_exists($id, $id_map)) {
                    $cw = &$list[$id_map[$id]];
                    $cw->weather_text = $w->weather_text;
                    $cw->weather_icon = $w->weather_icon;
                    $cw->temperature = $w->temperature;
                    $cw->temperature_max = $w->temperature_max;
                    $cw->temperature_min = $w->temperature_min;
                    $cw->wind_speed = $w->wind_speed;
                    $cw->wind_degree = $w->wind_degree;
                    $cw->created_at = $w->created_at;
                    $cw->updated_at = $w->updated_at;
                    $cw->status = 2;
                }
            }
        }

        return $list;
    }

    /**
     * 指定したIDの気象情報をAPIから取得
     * 取得に失敗した場合は空の配列を返す
     *
     * @param array $id_map
     * @return Weathers[]
     */
    public function getWeather($id_map)
    {
        try {
            // 気象API取得
            $app_key = env('WEATHER_API_KEY');
            $url = "http://api.openweathermap.org/data/2.5/group?APPID=${app_key}&units=metric&id=".join(',', $id_map);

            $conn = curl_init();
            curl_setopt($conn, CURLOPT_URL, $url);
            curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
            $str = curl_exec($conn);
            curl_close($conn);

            $json = json_decode($str, true);
        } catch (Exception $e) {
            // 気象API取得エラー
            return [];
        }

        // キーを辿ってデータを返す ない場合はnullを返す
        $json_find = function ($data, $keys) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $data)) {
                    $data = $data[$key];
                } else {
                    return null;
                }
            }
            return $data;
        };

        $res = [];
        try {
            // 取得データのDB登録
            DB::beginTransaction();
            foreach ($json['list'] as $data) {
                $w = Weather::firstOrNew(['id' => $data['id']]);
                $w->weather_text = $json_find($data, ['weather', 0, 'main']);
                $w->weather_icon = $json_find($data, ['weather', 0, 'icon']);
                $w->temperature = $json_find($data, ['main', 'temp']);
                $w->temperature_max = $json_find($data, ['main', 'temp_max']);
                $w->temperature_min = $json_find($data, ['main', 'temp_min']);
                $w->wind_speed =  $json_find($data, ['wind', 'speed']);
                $w->wind_degree = $json_find($data, ['wind', 'deg']);
                $w->save();
                $res[$data['id']] = $w;
            }
            DB::commit();
        } catch (Exception $e) {
            // 登録エラー
            DB::rollBack();
            return [];
        }

        return $res;
    }
}
