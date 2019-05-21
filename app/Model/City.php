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

    public function wheather()
    {
        return $this->hasOne('App\Model\Weather', 'id', 'id');
    }
}
