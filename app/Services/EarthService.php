<?php

namespace App\Services;

use DB;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;

class EarthService
{
    /**
     * Undocumented function
     */
    public function __construct()
    { }

    /**
     * 緯度経度取得
     *
     * @param int $region_id
     * @param int $country_id
     * 
     * @return array
     */
    public function getCoordinate($region_id = null, $country_id = null)
    {
        $emphasis = '1 = 1';
        if (!is_null($region_id)) {
            $emphasis = "region_id = ${region_id}";
        } elseif (!is_null($country_id)) {
            $emphasis = "id = ${country_id}";
        }
        $select_sql = <<<CASE
floor(coord_lon) as lon,
floor(coord_lat) as lat,
count(country_code in (
    select code
    from country
    where ${emphasis}
) or null) as emphasis
CASE;
        /**
         * @var Builder $query
         */
        $query = DB::table('city')
            ->select(DB::raw($select_sql))
            ->groupBy(DB::raw('floor(city.coord_lon), floor(city.coord_lat)'));
        $list = $query->get();
        Collection::macro('shrink', function () {
            return $this->map(function ($v) {
                return [$v->lon,$v->lat,$v->emphasis];
            });
        });
        return collect($list)->shrink();
    }
}
