<?php

namespace App\Repositories;

use App\Contracts\Repositories\OblastRepositoryInterface;
use App\DTOs\CoordinatesDTO;
use App\Models\Oblast;
use App\Models\Polygon;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentOblastRepository implements OblastRepositoryInterface
{
    public function findAllByCoordinates(CoordinatesDTO $coordinates): Collection
    {
        return Oblast::whereHas('polygon', function (Builder $query) use ($coordinates) {
            $query->whereRaw(
                'ST_Contains(ST_GeomFromGeoJSON(geojson, 1, 4326), ST_SRID(ST_GeomFromText(?), 4326))',
                [$this->point($coordinates)]
            );
        })->get();
    }

    public function findByCoordinates(CoordinatesDTO $coordinates): ?Oblast
    {
        return Oblast::whereHas('polygon', function (Builder $query) use ($coordinates) {
            $query->whereRaw(
                'ST_Contains(ST_GeomFromGeoJSON(geojson, 1, 4326), ST_SRID(ST_GeomFromText(?), 4326))',
                [$this->point($coordinates)]
            );
        })->first();
    }

    public function create(array $data): Oblast
    {
        return DB::transaction(function () use ($data) {
            $oblast = Oblast::create($data);
            $polygon = $data['polygon'] ?? null;
            unset($data['polygon']);

            if ($polygon) {
                $oblast->setPolygon($polygon);
            }

            return $oblast;
        });
    }

    public function truncate(): void
    {
        Polygon::where('polygonable_type', 'oblast')->delete();
        Oblast::truncate();
    }

    public function bulkUpsert(array $oblasts): void
    {
        foreach (array_chunk($oblasts, 100) as $chunk) {
            DB::transaction(function () use ($chunk) {
                foreach ($chunk as $oblastData) {
                    $polygon = $oblastData['polygon'] ?? null;
                    unset($oblastData['polygon']);

                    $oblast = Oblast::updateOrCreate(
                        [
                            'provider_name' => $oblastData['provider_name'] ?? null,
                            'provider_id' => $oblastData['provider_id'] ?? null,
                        ],
                        $oblastData
                    );

                    if ($polygon) {
                        $oblast->setPolygon($polygon);
                    }
                }
            });
        }
    }

    private function point(CoordinatesDTO $coordinates): string
    {
        return sprintf('POINT(%f %f)', $coordinates->longitude, $coordinates->latitude);
    }
}
