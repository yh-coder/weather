<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;
use Request;
use Response;
use App\Services\WeatherService;

class WeatherController extends Controller
{
    protected $weather_service;

    public function __construct(WeatherService $weather_service)
    {
        $this->weather_service = $weather_service;
    }

    /**
     * 地域リスト
     *
     * @return void
     */
    public function region()
    {
        // 地域列挙
        $regions = $this->weather_service->getRegions();

        return view('weather.region', compact('regions', 'countries'));
    }

    /**
     * 国リスト
     *
     * @param integer $region_id
     * @return void
     */
    public function country(int $region_id)
    {
        // 地域取得
        $region = $this->weather_service->getRegion($region_id);
        // 国列挙
        $countries = $this->weather_service->getCountries(
            config('constants.country_page_size'),
            $region_id
        );
        // 地域・国が見つからない場合
        if (empty($region) || $countries->isEmpty()) {
            abort('404');
        }

        return view('weather.country', compact('region', 'countries'));
    }

    /**
     * 観測点リスト
     *
     * @param integer $region_id
     * @param string $country_code
     * @return void
     */
    public function city(int $region_id, string $country_id)
    {
        // 地域取得
        $region = $this->weather_service->getRegion($region_id);
        // 国取得
        $country = $this->weather_service->getCountry($region_id, $country_id);
        // 国が見つからない場合
        if (empty($country)) {
            abort('404');
        }
        // 観測点検索文字列
        $find_name = Request::get('q');
        // 観測点取得
        $cities = $this->weather_service->getCityWeather(
            config('constants.city_page_size'),
            $country_id,
            $find_name
        );

        return view('weather.city', compact('region', 'country', 'cities', 'find_name'));
    }

    /**
     * 観測点リスト
     *
     * @param integer $region_id
     * @param string $country_code
     * @return void
     */
    public function forecast(int $region_id, string $country_id, int $city_id)
    {
        // 地域取得
        $region = $this->weather_service->getRegion($region_id);
        // 国取得
        $country = $this->weather_service->getCountry($region_id, $country_id);
        // 国が見つからない場合
        if (empty($country)) {
            abort('404');
        }
        // 観測点が見つからない場合
        $city = $this->weather_service->getCity($city_id, $country->code);
        if (empty($city)) {
            abort('404');
        }
        // 天気予報取得
        $forecast = $this->weather_service->getCityForecast($city_id);
        
        return view('weather.forecast', compact('region', 'country', 'city', 'forecast'));
    }
}
