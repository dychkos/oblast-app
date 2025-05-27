<?php

namespace App\DTOs;

class CoordinatesDTO
{
    public function __construct(
        public float $latitude,
        public float $longitude
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            latitude: (float) $data['lat'],
            longitude: (float) $data['lon']
        );
    }
}
