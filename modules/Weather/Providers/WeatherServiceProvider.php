<?php
namespace Modules\Weather\Providers;

use Illuminate\Support\ServiceProvider;

class WeatherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \Modules\Weather\Contracts\Interfaces\WeatherServiceInterface::class,
            \Modules\Weather\Services\OpenWeatherService::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . "/../Routes/api.php");
    }
}
