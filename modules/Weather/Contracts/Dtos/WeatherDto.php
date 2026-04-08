<?php
namespace Modules\Weather\Contracts\Dtos;

class WeatherDto
{
    public function __construct(
        public string $city,
        public float $temperature,
        public string $description,
        public int $timestamp,
        public string $source
    ) {
    }

    public function toArray(): array
    {
        return [
            'city' => $this->city,
            'temperature' => $this->temperature,
            'description' => $this->description,
            'timestamp' => $this->timestamp,
            'source' => $this->source,
        ];
    }
}
