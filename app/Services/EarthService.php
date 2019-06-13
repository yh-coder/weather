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
     * @return array
     */
    public function getEarth()
    {
        $select_sql = <<<CASE
    floor(city.coord_lon) as lon,
    floor(city.coord_lat) as lat,
    country.region_id as region_id,
    country.id as country_id
CASE;
        /**
         * @var Builder $query
         */
        $query = DB::table('city')
            ->select(DB::raw($select_sql))->distinct()
            ->join('country', 'city.country_code', '=', 'country.code')
            ->orderBy(DB::raw('floor(city.coord_lon), floor(city.coord_lat), country.region_id, country.id'));

        $list = [];
        $tmp = [null, null, null];
        foreach ($query->get() as $v) {
            if ($tmp[0] == $v->lon && $tmp[1] == $v->lat) {
                if ($tmp[2] != $v->region_id) {
                    $regions[] = $v->region_id;
                }
                $countries[] = $v->country_id;
            } else {
                if (!is_null($tmp[0])) {
                    $list[] = [[$tmp[0], $tmp[1]], $regions, $countries];
                }
                $tmp[0] = $v->lon;
                $tmp[1] = $v->lat;
                $tmp[2] = $v->region_id;
                $regions = [$v->region_id];
                $countries = [$v->country_id];
            }
        }
        $list[] = [[$tmp[0], $tmp[1]], $regions, $countries];
        return $list;
    }

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
                return [$v->lon, $v->lat, $v->emphasis];
            });
        });
        return collect($list)->shrink();
    }
}
