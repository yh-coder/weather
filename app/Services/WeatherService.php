<?php

namespace App\Services;

use DB;

use App\Model\Region;
use App\Model\Country;
use App\Model\City;

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
    public function getRegion($id=null)
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
        return City::where('country_code', '=', $country_code)
            ->orderBy('name')
            ->paginate(20);
    }
}
