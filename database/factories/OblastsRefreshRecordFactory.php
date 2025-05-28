<?php

namespace Database\Factories;

use App\Enums\RefreshJobStateEnum;
use App\Models\OblastsRefreshRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class OblastsRefreshRecordFactory extends Factory
{
    protected $model = OblastsRefreshRecord::class;

    public function definition(): array
    {
        return [
            'state' => $this->faker->randomElement([RefreshJobStateEnum::values()]),
            'create_ts' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            'delay_ts' => $this->faker->dateTimeBetween('-45 minutes', 'now'),
        ];
    }

    public function queued(): self
    {
        return $this->state(fn (array $attributes) => [
            'state' => RefreshJobStateEnum::PENDING->value,
        ]);
    }

    public function processing(): self
    {
        return $this->state(fn (array $attributes) => [
            'state' => RefreshJobStateEnum::PROCESSING->value,
        ]);
    }

    public function done(): self
    {
        return $this->state(fn (array $attributes) => [
            'state' => RefreshJobStateEnum::DONE->value,
        ]);
    }

    public function failed(): self
    {
        return $this->state(fn (array $attributes) => [
            'state' => RefreshJobStateEnum::FAILED->value,
        ]);
    }

    public function withDelay(int $delay): self
    {
        return $this->state(fn (array $attributes) => [
            'delay_ts' => now()->addSeconds($delay),
        ]);
    }
}
