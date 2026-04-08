<?php
namespace Modules\Weather\Contracts\Interfaces;

use Modules\Weather\Contracts\Dtos\WeatherDto;

interface WeatherServiceInterface
{
    /**
     * @throws \Modules\Weather\Exceptions\WeatherServiceExceptions
     */
    public function getWeatherData(string $city): WeatherDto;
    public function getCachedWeatherData(string $city): WeatherDto;

}