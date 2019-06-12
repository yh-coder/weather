<?php

namespace App\Http\Controllers;

use Request;
use Response;
use App\Services\EarthService;

class EarthController extends Controller
{
    /**
     * Undocumented variable
     *
     * @var EarthService $earth_service
     */
    protected $earth_service;

    public function __construct(EarthService $earth_service)
    {
        $this->earth_service = $earth_service;
    }

    
    /**
     * 座標取得
     *
     * @return void
     */
    public function coordinate()
    {
        $region_id = Request::get('region_id');
        $country_id = Request::get('country_id');

        // 座標取得
        $list = $this->earth_service->getCoordinate($region_id, $country_id);

        return response()->json(['coordinate' => $list]);
    }
}