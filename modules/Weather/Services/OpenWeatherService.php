<?php
namespace Modules\Weather\Services;

use Modules\Weather\Contracts\Dtos\WeatherDto;
use Modules\Weather\Contracts\Interfaces\WeatherServiceInterface;
use Modules\Weather\Exceptions\CityNotFoundException;
use Modules\Weather\Exceptions\WeatherServiceExceptions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OpenWeatherService implements WeatherServiceInterface
{
    private int $cacheTime = 600;
    protected $urls = ["currentWeather" => "https://api.openweathermap.org/data/2.5/weather"];

    public function mapToWeatherDto(array $data): WeatherDto
    {
        return new WeatherDto(
            city: $data['name'],
            temperature: (float) $data['main']['temp'],
            description: $data['weather'][0]['description'],
            timestamp: (int) $data['dt'],
            source: $data['source'] ?? 'external'
        );
    }

    public function getWeatherData(string $city): WeatherDto
    {
        try {
            $key = config('services.openWeather.key');
            $url = $this->urls['currentWeather'] ?? '';

            $response = Http::timeout(config('services.openWeather.timeout'))
                ->retry(2, 300, null, false)
                ->get($url, [
                    'q' => $city,
                    'appid' => $key,
                    'units' => 'metric'
                ]);
        } catch (\Exception $e) {
            throw new WeatherServiceExceptions('Api is unavailable', $e->getCode());
        }

        if ($response->failed()) {
            $errorData = $response->json();
            $message = $errorData['message'] ?? 'Unknown external API error';
            $code = $response->getStatusCode();

            match ($code) {
                404 => throw new CityNotFoundException($message, $code),
                default => throw new WeatherServiceExceptions($message, $code),
            };
        }
        return $this->mapToWeatherDto($response->json());
    }

    public function getCachedWeatherData(string $city): WeatherDto
    {
        $cacheKey = "weather_" . strtolower(trim($city));
        $hasCache = Cache::has($cacheKey);
        if ($hasCache) {
            $cache = (array) Cache::get($cacheKey);
            return new WeatherDto(
                city: $cache["city"],
                temperature: $cache['temperature'],
                description: $cache['description'],
                timestamp: $cache['timestamp'],
                source: 'cache',
            );
        }
        $weather = $this->getWeatherData($city);
        Cache::put($cacheKey, $weather, $this->cacheTime);
        return $weather;
    }
}