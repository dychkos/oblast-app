<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OblastFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->city;

        return [
            'name' => strtolower(str_replace(' ', '_', $name)),
            'display_name' => $name,
            'lat' => $this->faker->latitude,
            'lon' => $this->faker->longitude,
            'provider_name' => $this->faker->word,
            'provider_id' => $this->faker->uuid,
        ];
    }

    public function withPolygon()
    {
        return $this->afterCreating(function ($oblast) {
            $polygon = [
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [$oblast->lon - 0.1, $oblast->lat - 0.1],
                        [$oblast->lon + 0.1, $oblast->lat - 0.1],
                        [$oblast->lon + 0.1, $oblast->lat + 0.1],
                        [$oblast->lon - 0.1, $oblast->lat + 0.1],
                        [$oblast->lon - 0.1, $oblast->lat - 0.1],
                    ]
                ]
            ];

            $oblast->setPolygon($polygon);
        });
    }

    public function withCoordinates(float $lat, float $lon)
    {
        return $this->state([
            'lat' => $lat,
            'lon' => $lon,
        ]);
    }
}
