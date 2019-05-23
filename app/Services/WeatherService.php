<?php

namespace App\Services;

use DB;

use App\Model\Region;
use App\Model\Country;
use App\Model\City;
use App\Model\Weather;

use \Datetime;
use \DateTimeZone;
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
     * @param string $region_id
     * @param string $country_id
     * @return Country
     */
    public function getCountry($region_id, $country_id)
    {
        return Country::where('region_id', '=', $region_id)
            ->where('id', '=', $country_id)
            ->first();
    }

    /**
     * 地域とコードを指定し国を取得
     *
     * @param string $country_id
     * @return Country
     */
    public function getCity($city_id, $country_code)
    {
        return City::where('country_code', '=', $country_code)
            ->where('id', '=', $city_id)
            ->first();
    }

    /**
     * 観測点と気象情報を返す
     * 更新に失敗したデータはstatusが2以外
     *
     * @param int $page_size
     * @param string $country_code
     * @param string|null $find_name
     * @return void
     */
    public function getCityWeather($page_size, $country_id, $find_name = null)
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

        // 観測点と天気の情報をDBから取得
        $query = DB::table('city')
            ->join('country', 'city.country_code', '=', 'country.code')
            ->leftJoin('weather', function ($join) {
                $join->on('city.id', '=', 'weather.city_id')->where('weather.branch', '=', 0);
            })
            ->where('country.id', '=', $country_id)
            ->orderBy('name')
            ->select(['city.*', 'weather.*', DB::raw($case_sql), 'city.id AS id']); // idが上書きされるため最後に
        if (!empty($find_name)) {
            $query->where('city.name', 'like', "%${find_name}%");
        }
        $list = $query->paginate($page_size);
        
        // 更新が必要なIDを抽出 ついでに予報日をDatetimeに
        $city_id_map = [];
        foreach ($list as $i => &$cw) {
            $cw->forecast_at = new Datetime($cw->forecast_at);
            if ($cw->status!==2) {
                $city_id_map[$i] = $cw->id;
            }
        }

        // 更新が必要なIDがあれば取得しステータス更新
        if (!empty($city_id_map)) {
            $weathers = $this->getApiWeather($city_id_map);
            // kv入れ替え
            $city_id_map = array_flip($city_id_map);
            foreach ($weathers as $id => $w) {
                if (array_key_exists($id, $city_id_map)) {
                    $cw = &$list[$city_id_map[$id]];
                    $cw->city_id = $w->city_id;
                    $cw->branch = $w->branch;
                    $cw->weather_text = $w->weather_text;
                    $cw->weather_text = $w->weather_text;
                    $cw->weather_icon = $w->weather_icon;
                    $cw->temperature = $w->temperature;
                    $cw->temperature_max = $w->temperature_max;
                    $cw->temperature_min = $w->temperature_min;
                    $cw->wind_speed = $w->wind_speed;
                    $cw->wind_degree = $w->wind_degree;
                    $cw->forecast_at = $w->forecast_at;
                    $cw->created_at = $w->created_at;
                    $cw->updated_at = $w->updated_at;
                    $cw->status = 2;
                }
            }
        }

        return $list;
    }

    /**
     * APIからcity_idの気象情報を取得しDB更新
     * 取得に失敗した場合は空の配列を返す
     *
     * @param array $city_id_map
     * @return Weathers[]
     */
    public function getApiWeather($city_id_map)
    {
        // return []
        try {
            // 気象API取得
            $app_key = env('WEATHER_API_KEY');
            $url = "http://api.openweathermap.org/data/2.5/group?APPID=${app_key}&units=metric&id=".join(',', $city_id_map);

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

        try {
            // 取得データのDB登録
            $res = [];
            DB::beginTransaction();
            foreach ($json['list'] as $data) {
                $w = Weather::firstOrNew(['city_id' => $data['id'], 'branch'=>0]);
                $w->city_id = $data['id'];
                $w->branch = 0;
                $w->weather_text = $this::json_find($data, ['weather', 0, 'main']);
                $w->weather_icon = $this::json_find($data, ['weather', 0, 'icon']);
                $w->temperature = $this::json_find($data, ['main', 'temp']);
                $w->temperature_max = $this::json_find($data, ['main', 'temp_max']);
                $w->temperature_min = $this::json_find($data, ['main', 'temp_min']);
                $w->wind_speed =  $this::json_find($data, ['wind', 'speed']);
                $w->wind_degree = $this::json_find($data, ['wind', 'deg']);
                if (array_key_exists('dt', $data)) {
                    $w->forecast_at = (new Datetime("@".$data['dt']))->setTimezone(new DateTimeZone('Asia/Tokyo'));
                }
                $w->updated_at = new Datetime();
                $w->save();
                $res[$w->city_id] = $w;
            }
            DB::commit();
            return $res;
        } catch (Exception $e) {
            // 登録エラー
            DB::rollBack();
            return [];
        }
    }

    /**
     * 観測点の天気予報を返す
     * 更新に失敗したデータはstatusが2以外
     *
     * @param int $city_id
     * @return void
     */
    public function getCityForecast($city_id)
    {
        // 観測点の天気予報をDBから取得

        // DBにある気象情報のステータス
        // 0: 該当なし、1: 更新から24時間経過、2:更新から24時間以内
        $case_sql = <<<CASE
case
    when weather.updated_at is null then 0
    when date_add(weather.updated_at, interval 24 hour) < now() then 1
    else 2
end as status
CASE;

        // 観測点と天気の情報をDBから取得
        $w = DB::table('city')
            ->leftJoin('weather', function ($join) {
                $join->on('city.id', '=', 'weather.city_id')->where('weather.branch', '=', 1);
            })
            ->where('city.id', '=', $city_id)
            ->select([DB::raw($case_sql)])
            ->first();

        // DBの天気予報が24時間以内の場合
        if ($w->status==2) {
            // DBから返す
            return ['status'=>$w->status, 'list'=>Weather::where('city_id', '=', $city_id)->where('branch', '>', 0)->get()];
        }

        // APIからデータを取得する
        $res = $this->getApiForecast($city_id);
        // APIから取得失敗の場合
        if (is_null($res)) {
            // DBから返す
            return ['status'=>$w->status, 'list'=>Weather::where('city_id', '=', $city_id)->where('branch', '>', 0)->get()];
        }
        return ['status'=>2, 'list'=>$res];
    }

    /**
     * APIからcity_idの天気予報を取得しDB更新
     * 取得に失敗した場合は空の配列を返す
     *
     * @param array $city_id_map
     * @return Weathers[]
     */
    public function getApiForecast($city_id)
    {
        // return null;
        try {
            // 気象API取得
            $app_key = env('WEATHER_API_KEY');
            $url = "http://api.openweathermap.org/data/2.5/forecast?APPID=${app_key}&units=metric&id=$city_id";

            $conn = curl_init();
            curl_setopt($conn, CURLOPT_URL, $url);
            curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
            $str = curl_exec($conn);
            curl_close($conn);

            $json = json_decode($str, true);
        } catch (Exception $e) {
            // 気象API取得エラー
            return null;
        }

        try {
            // 取得データのDB登録
            $res = [];
            DB::beginTransaction();
            $branch = 0;
            foreach ($json['list'] as $data) {
                $w = Weather::firstOrNew(['city_id' => $city_id, 'branch'=>$branch+1]);
                $w->city_id = $city_id;
                $w->branch = $branch+1;
                $w->weather_text = $this::json_find($data, ['weather', 0, 'main']);
                $w->weather_icon = $this::json_find($data, ['weather', 0, 'icon']);
                $w->temperature = $this::json_find($data, ['main', 'temp']);
                $w->temperature_max = $this::json_find($data, ['main', 'temp_max']);
                $w->temperature_min = $this::json_find($data, ['main', 'temp_min']);
                $w->wind_speed =  $this::json_find($data, ['wind', 'speed']);
                $w->wind_degree = $this::json_find($data, ['wind', 'deg']);
                if (array_key_exists('dt', $data)) {
                    $w->forecast_at = (new Datetime("@".$data['dt']))->setTimezone(new DateTimeZone('Asia/Tokyo'));
                }
                $w->updated_at = new Datetime();
                $w->save();
                $res[] = $w;
                ++$branch;
            }
            // 取得を超える分は削除
            Weather::where('city_id', '=', $city_id)->where('branch', '>', $branch)->delete();
            DB::commit();
            return $res;
        } catch (Exception $e) {
            // 登録エラー
            DB::rollBack();
            return null;
        }
    }

    /**
     * ツリーを辿ってデータを返す ない場合はnullを返す
     *
     * @param array $json
     * @param int|string $keys
     * @return mixed
     */
    static function json_find($json, $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $json)) {
                $json = $json[$key];
            } else {
                return null;
            }
        }
        return $json;
    }
}
