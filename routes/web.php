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

// 天気予報
Route::get('/{region_id}/{country_id}/{city_id}', 'WeatherController@forecast')
    ->where(['region_id'=>'[0-9]+', 'country_id'=>'[0-9]+', 'city_id'=>'[0-9]+'])
    ->name('weather.forecast');

// 観測点リスト
Route::get('/{region_id}/{country_id}/{q?}{page?}', 'WeatherController@city', ['q'=>'find_name'])
    ->where(['region_id'=>'[0-9]+', 'country_id'=>'[0-9]+'])
    ->name('weather.city');

// 国リスト
Route::get('/{region_id}', 'WeatherController@country')
    ->where(['region_id'=>'[0-9]+'])
    ->name('weather.country');

// 地域リスト
Route::get('/', 'WeatherController@region')
    ->name('weather.region');

// Aboutページ
Route::view('/about', 'weather.about')
    ->name('weather.about');

/**
 * Earth Api
 */
// 座標取得
Route::get('/api/earth', 'EarthController@coordinate')->name('api.earth.coordinate');
