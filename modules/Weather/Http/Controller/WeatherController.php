<?php
namespace Modules\Weather\Http\Controller;

use Illuminate\Http\JsonResponse;
use Modules\Weather\Contracts\Interfaces\WeatherServiceInterface;
use Modules\Weather\Http\Requests\GetWeatherRequest;

class WeatherController
{
    public function __construct(protected WeatherServiceInterface $weatherService)
    {
    }
    public function showCurrentCityWeather(GetWeatherRequest $request, string $city): JsonResponse
    {
        $city = $request->validated()["city"] ?? $city;
        $weather = $this->weatherService->getWeatherData($city);
        // I'm thinking we could cache it on this request just so we can 
        // immediately use it when we use the cached route but out of scope for now
        return response()->json($weather->toArray());
    }

    public function showCachedCurrentCityWeather(GetWeatherRequest $request, string $city): JsonResponse
    {
        $city = $request->validated()["city"] ?? $city;
        $weather = $this->weatherService->getCachedWeatherData($city);
        return response()->json($weather->toArray());
    }
}