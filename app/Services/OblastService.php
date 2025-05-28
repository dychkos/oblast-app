<?php

namespace App\Services;

use App\Contracts\Repositories\OblastRepositoryInterface;
use App\DTOs\CoordinatesDTO;
use App\DTOs\OblastSearchResultDTO;
use Illuminate\Support\Facades\Cache;

class OblastService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'oblasts';
    private const CACHE_VERSION_KEY = 'oblasts:version';

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
        $this->invalidateAllCache();
    }

    public function upsertOblastsData(array $oblasts): void
    {
        $this->oblastRepository->bulkUpsert($oblasts);
        $this->invalidateAllCache();
    }

    private function invalidateAllCache(): void
    {
        $this->incrementCacheVersion();
    }

    private function incrementCacheVersion(): void
    {
        $currentVersion = $this->getCacheVersion();
        $newVersion = $currentVersion + 1;

        Cache::put(self::CACHE_VERSION_KEY, $newVersion, now()->addDay());
    }

    private function getCacheVersion(): int
    {
        return Cache::get(self::CACHE_VERSION_KEY, 1);
    }


    private function generateCacheKey(CoordinatesDTO $coordinates): string
    {
        $version = $this->getCacheVersion();
        return self::CACHE_PREFIX . ":{$coordinates->latitude}:{$coordinates->longitude}:v{$version}";
    }
}
