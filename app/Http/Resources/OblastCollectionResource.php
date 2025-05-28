<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OblastCollectionResource extends ResourceCollection
{
    public $collects = OblastResource::class;

    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    public function jsonOptions(): int
    {
        return JSON_UNESCAPED_UNICODE;
    }
}
