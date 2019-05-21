<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Region List
Route::get('/', 'WeatherController@region')->name('weather.region');

// Country List
Route::get('/{region}', 'WeatherController@country')->name('weather.country');

// City List
Route::get('/{region}/{country}/{page?}', 'WeatherController@city')->name('weather.city');

