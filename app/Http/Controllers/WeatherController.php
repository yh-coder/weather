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
     * 地域一覧
     *
     * @return void
     */
    public function region()
    {
        $regions = $this->weather_service->getRegion();

        return view('weather.region', compact('regions', 'countries'));
    }

    /**
     * 地域の国一覧
     *
     * @param integer $region_id
     * @return void
     */
    public function country(int $region_id)
    {
        // 地域取得
        $region = $this->weather_service->getRegion($region_id);
        // 地域選択
        $countries = $this->weather_service->getCountries($region_id);

        if ($region->isEmpty() || $countries->isEmpty()) {
            return redirect('/');
        }

        return view('weather.country', compact('countries'))->with([
            'region'=>$region[0]
        ]);
    }

    /**
     * 都市一覧
     *
     * @param integer $region_id
     * @param string $country_code
     * @param integer|null $page
     * @return void
     */
    public function city(int $region_id, string $country_code, $page = null)
    {
        // 地域取得
        $region = $this->weather_service->getRegion($region_id);
        // 地域選択
        $country = $this->weather_service->getCountry($country_code);
        // 都市選択
        $cities = $this->weather_service->getCity($country_code, $page);

        if ($region->isEmpty() || $country->isEmpty() || $cities->isEmpty()) {
            return redirect('/');
        }

        // ID不一致
        if ($region[0]->id != $country[0]->region_id) {
            return redirect('/'.$region[0]->id);
        }

        return view('weather.city', compact('cities'))->with([
            'region'=>$region[0],
            'country'=>$country[0],
        ]);
    }
}
