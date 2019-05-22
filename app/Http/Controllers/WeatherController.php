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
     * @param integer|null $page
     * @return void
     */
    public function country(int $region_id, $page=null)
    {
        // 地域取得
        $region = $this->weather_service->getRegion($region_id);
        // 国列挙
        $countries = $this->weather_service->getCountries(
            config('constants.country_page_size'),
            $region_id
        );

        // 地域・国が見つからない場合、地域リストに遷移
        if (empty($region) || $countries->isEmpty()) {
            return redirect('/');
        }

        return view('weather.country', compact('region', 'countries'));
    }

    /**
     * 観測点リスト
     *
     * @param integer $region_id
     * @param string $country_code
     * @param integer|null $page
     * @return void
     */
    public function city(int $region_id, string $country_code, $page=null)
    {
        // 地域取得
        $region = $this->weather_service->getRegion($region_id);
        // 国取得
        $country = $this->weather_service->getCountry($region_id, $country_code);
        // 国が見つからない場合、国リストに遷移
        if (empty($country)) {
            return redirect('/'.$region->id);
        }

        // 都市選択
        $cities = $this->weather_service->getCityWeather(
            config('constants.city_page_size'),
            $country_code
        );

        return view('weather.city', compact('region', 'country', 'cities'));
    }
}
