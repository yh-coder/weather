<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relation;

class Region extends Model
{
    protected $table = 'region';

    protected $filltable = [
        'id',
        'name',
    ];
}
