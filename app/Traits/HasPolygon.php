<?php

namespace App\Traits;

use App\Models\Polygon;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use JsonException;

trait HasPolygon
{
    public function polygon(): MorphOne
    {
        return $this->morphOne(Polygon::class, 'polygonable');
    }

    public function getPolygonGeojson(): ?array
    {
        return optional($this->polygon)->geojson ? json_decode($this->polygon->geojson, true) : null;
    }

    public function setPolygon(array $geojson): Polygon
    {
        $normalized = self::normalizePolygon($geojson);

        return $this->polygon()->updateOrCreate([], [
            'geojson' => $normalized,
        ]);
    }

    /**
     * @throws JsonException
     */
    public static function normalizePolygon(array $geojson): string
    {
        if (
            isset($geojson['type'], $geojson['coordinates']) &&
            is_string($geojson['type']) &&
            is_array($geojson['coordinates'])
        ) {
            return json_encode([
                'type' => $geojson['type'],
                'coordinates' => $geojson['coordinates'],
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        throw new \InvalidArgumentException('Invalid geojson structure');
    }

}
