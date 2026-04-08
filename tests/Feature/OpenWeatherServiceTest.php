<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Modules\Weather\Services\OpenWeatherService;
use Modules\Weather\Exceptions\CityNotFoundException;
use Modules\Weather\Exceptions\WeatherServiceExceptions;
use Modules\Weather\Contracts\Dtos\WeatherDto;

beforeEach(function () {
    $this->service = new OpenWeatherService();
});

describe('Successful fetches', function () {
    $validApiResponse = [
        'name' => 'London',
        'main' => ['temp' => 15.5],
        'weather' => [['description' => 'clear sky']],
        'dt' => 1620000000,
    ];

    it('maps array to WeatherDto correctly', function () use ($validApiResponse) {
        $dto = $this->service->mapToWeatherDto($validApiResponse);

        expect($dto)->toBeInstanceOf(WeatherDto::class);

        expect($dto->city)->toBe('London')
            ->and($dto->temperature)->toBe(15.5)
            ->and($dto->description)->toBe('clear sky')
            ->and($dto->timestamp)->toBe(1620000000)
            ->and($dto->source)->toBe('external');
    });
    it('fetches current weather successfully from API', function () use ($validApiResponse) {
        Http::fake([
            'api.openweathermap.org/*' => Http::response($validApiResponse, 200),
        ]);

        $dto = $this->service->getWeatherData('London');

        expect($dto)->toBeInstanceOf(WeatherDto::class)
            ->and($dto->city)->toBe('London');
    });

    it('caches the weather data and updates the source on subsequent calls', function () use ($validApiResponse) {
        Http::fake([
            'api.openweathermap.org/*' => Http::response($validApiResponse, 200),
        ]);

        Cache::flush();

        $firstCallDto = $this->service->getCachedWeatherData('London');
        expect($firstCallDto->source)->toBe('external');

        $secondCallDto = $this->service->getCachedWeatherData('London');
        expect($secondCallDto->source)->toBe('cache');

        Http::assertSentCount(1);
    });

});

describe('Exceptions', function () {
    it('throws CityNotFoundException when API returns 404', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'city not found'], 404),
        ]);

        $this->service->getWeatherData('UnknownCity');
    })->throws(CityNotFoundException::class, 'city not found');

    it('throws WeatherServiceExceptions for other API errors', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'Internal server error'], 500),
        ]);

        $this->service->getWeatherData('London');
    })->throws(WeatherServiceExceptions::class, 'Internal server error');

    it('throws WeatherServiceExceptions when HTTP request fails entirely (e.g. timeout)', function () {
        Http::fake(fn() => throw new \Exception('Connection timeout', 504));

        $this->service->getWeatherData('London');
    })->throws(WeatherServiceExceptions::class, 'Api is unavailable');


    it('throws WeatherServiceExceptions when API key is invalid', function () {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'Invalid API key'], 401),
        ]);

        $this->service->getWeatherData('London');
    })->throws(WeatherServiceExceptions::class, 'Invalid API key');
});