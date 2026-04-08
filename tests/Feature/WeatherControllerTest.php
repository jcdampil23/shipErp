<?php

use Modules\Weather\Contracts\Interfaces\WeatherServiceInterface;
use Modules\Weather\Contracts\Dtos\WeatherDto;
use function Pest\Laravel\getJson;

beforeEach(function () {
    $this->mockDto = new WeatherDto(
        city: 'London',
        temperature: 15.5,
        description: 'clear sky',
        timestamp: 1620000000,
        source: 'external'
    );
});


describe('Successful returns', function () {
    it('returns current city weather as JSON', function () {
        $this->mock(WeatherServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getWeatherData')
                ->once()
                ->with('London')
                ->andReturn($this->mockDto);
        });

        getJson(route('City Weather', ['city' => 'London']))
            ->assertOk()
            ->assertJson($this->mockDto->toArray());
    });

    it('returns cached city weather as JSON', function () {
        $cachedDto = clone $this->mockDto;
        $cachedDto->source = 'cache';

        $this->mock(WeatherServiceInterface::class, function ($mock) use ($cachedDto) {
            $mock->shouldReceive('getCachedWeatherData')
                ->once()
                ->with('London')
                ->andReturn($cachedDto);
        });

        getJson(route('Cached City Weather', ['city' => 'London']))
            ->assertOk()
            ->assertJson((array) $cachedDto);
    });
});

describe('Validation Tests', function () {
    it('fails validation if city is too short', function () {
        getJson(route('City Weather', ['city' => 'a']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['city']);
    });

    it('fails validation if city contains numbers or special characters', function () {
        getJson(route('City Weather', ['city' => 'London123']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['city'])
            ->assertJsonFragment([
                'city' => ['City parameter has invalid characters']
            ]);
    });

    it('sanitizes and formats the city name correctly before processing', function () {
        $mockDto = new WeatherDto('New York', 15.5, 'clear sky', 1620000000, 'external');

        $this->mock(WeatherServiceInterface::class, function ($mock) use ($mockDto) {
            $mock->shouldReceive('getWeatherData')
                ->once()
                ->with('New York')
                ->andReturn($mockDto);
        });

        getJson(route('City Weather', ['city' => ' new-york  ']))
            ->assertOk();
    });
});