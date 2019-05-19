<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relation;

class Country extends Model
{
    protected $table = 'country';

    protected $filltable = [
        'id',
        'name',
        'code',
        'region_id'
    ];
}
