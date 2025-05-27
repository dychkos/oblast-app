<?php

namespace App\Services;

use App\Contracts\Repositories\OblastRepositoryInterface;
use App\DTOs\CoordinatesDTO;
use App\DTOs\OblastSearchResultDTO;
use Illuminate\Support\Facades\Cache;

class OblastService
{
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct(
        private readonly OblastRepositoryInterface $oblastRepository,
    ) {}

    public function findOblastsByCoordinates(CoordinatesDTO $coordinates): OblastSearchResultDTO
    {
        $cacheKey = $this->generateCacheKey($coordinates);
        $wasCached = Cache::has($cacheKey);

        $oblasts = Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            fn () => $this->oblastRepository->findAllByCoordinates($coordinates)->toArray()
        );

        return new OblastSearchResultDTO(
            data: $oblasts,
            cacheStatus: $wasCached ? 'hit' : 'miss'
        );
    }

    public function purgeAllData(): void
    {
        $this->oblastRepository->truncate();
//        $this->cacheService->flush();
    }

    public function upsertOblastsData(array $oblasts): void
    {
        $this->oblastRepository->bulkUpsert($oblasts);
//        $this->cacheService->flush();
    }

    private function generateCacheKey(CoordinatesDTO $coordinates): string
    {
        return "oblasts:{$coordinates->latitude}:{$coordinates->longitude}";
    }
}
