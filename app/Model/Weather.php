<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relation;

class Weather extends Model
{
    protected $table = 'weather';
    
    protected $fillable = ['id'];

    protected $filltable = [
        'id',
        'weather_text',
        'weather_icon',
        'temperature',
        'temperature_max',
        'temperature_min',
        'wind_speed',
        'wind_degree',
        'created_at',
        'updated_at'
    ];
}


