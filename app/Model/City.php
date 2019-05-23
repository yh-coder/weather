<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relation;

class City extends Model
{
    protected $table = 'city';

    protected $filltable = [
        'id',
        'name',
        'country_code',
        'coord_lon',
        'coord_lat'
    ];

    public function weather()
    {
        return $this->hasMany('App\Model\Weather', 'city_id', 'id');
    }
}
