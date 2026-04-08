<?php
namespace Modules\Weather\Routes;

use Illuminate\Support\Facades\Route;
use Modules\Weather\Http\Controller\WeatherController;

Route::group(['prefix' => 'weather'], function () {
    Route::get(
        '{city}',
        [WeatherController::class, 'showCurrentCityWeather']
    )->name('City Weather');
    Route::get(
        '{city}/cached',
        [WeatherController::class, 'showCachedCurrentCityWeather']
    )->name('Cached City Weather');
});