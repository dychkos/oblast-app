<?php

namespace App\DTOs;

use App\Http\Resources\OblastCollectionResource;

class OblastSearchResultDTO
{
    public function __construct(
        public array $data,
        public string $cacheStatus
    ) {}

    public function toResource(): OblastCollectionResource
    {
        return (new OblastCollectionResource($this->data))
            ->additional([
                'meta' => [
                    'cacheStatus' => $this->cacheStatus,
                ],
            ]);
    }
}
