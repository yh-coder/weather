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


// Aboutページ
Route::view('/about', 'weather.about')->name('weather.about');

// 地域リスト
Route::get('/', 'WeatherController@region')->name('weather.region');

// 国リスト
Route::get('/{region}', 'WeatherController@country')->name('weather.country');

// 観測点リスト
Route::get('/{region}/{country}/{page?}', 'WeatherController@city')->name('weather.city');
