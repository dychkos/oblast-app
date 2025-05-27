<?php

namespace App\Jobs;

use App\Contracts\OblastsApiProvider;
use App\Contracts\Repositories\OblastsRefreshRecordRepositoryInterface;
use App\Enums\RefreshJobStateEnum;
use App\Exceptions\NominatimApiException;
use App\Services\OblastService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class RefreshOblastDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 1;

    private const CHUNK_SIZE = 10;

    public function __construct(
        private readonly int $refreshJobId
    ) {}

    public function handle(
        OblastsRefreshRecordRepositoryInterface $refreshJobRepository,
        OblastsApiProvider $nominatimService,
        OblastService $oblastService
    ): void {
        $refreshJobRepository->updateState($this->refreshJobId, RefreshJobStateEnum::PROCESSING);

        try {
            Log::info('Starting oblast data refresh', ['job_id' => $this->refreshJobId]);

            $oblastNames = $nominatimService->getUkrainianOblastNames();

            if (empty($oblastNames)) {
                throw new \RuntimeException('No oblast names available for processing');
            }

            $totalCount = count($oblastNames);
            $processedCount = 0;
            $successCount = 0;
            $validationErrors = [];
            $hasAtLeastOneSuccess = false;

            foreach (array_chunk($oblastNames, self::CHUNK_SIZE) as $chunkIndex => $nameChunk) {
                $chunkData = [];

                foreach ($nameChunk as $oblastName) {
                    try {
                        $data = $nominatimService->fetchOblastPolygonData($oblastName);

                        if ($data) {
                            $chunkData[] = $data;
                        }
                    } catch (ValidationException $e) {
                        $validationErrors[$oblastName] = $e->errors();
                        Log::warning("Validation failed for {$oblastName}", [
                            'errors' => $e->errors(),
                            'job_id' => $this->refreshJobId,
                        ]);

                        continue;
                    } catch (NominatimApiException $e) {
                        Log::error("Skipping {$oblastName} due to API error", [
                            'error' => $e->getMessage(),
                            'job_id' => $this->refreshJobId,
                        ]);

                        continue;
                    }
                }

                if (! empty($chunkData)) {
                    $oblastService->upsertOblastsData($chunkData);

                    $successCount += count($chunkData);
                    $hasAtLeastOneSuccess = true;
                }

                $processedCount += count($nameChunk);

                Log::info("Processed chunk {$chunkIndex}", [
                    'job_id' => $this->refreshJobId,
                    'processed' => $processedCount,
                    'total' => $totalCount,
                    'success_count' => $successCount,
                    'validation_errors' => count($validationErrors),
                    'success_rate' => ($processedCount > 0) ? ($successCount / $processedCount) * 100 : 0,
                ]);
            }

            if (! empty($validationErrors)) {
                Log::warning('Completed with validation errors', [
                    'job_id' => $this->refreshJobId,
                    'total_validation_errors' => count($validationErrors),
                    'error_samples' => array_slice($validationErrors, 0, 5),
                ]);
            }

            if (! $hasAtLeastOneSuccess) {
                throw new \RuntimeException('No oblast data was successfully processed');
            }

            $refreshJobRepository->updateState($this->refreshJobId, RefreshJobStateEnum::DONE);

            Log::info('Oblast data refresh completed', [
                'job_id' => $this->refreshJobId,
                'total_processed' => $processedCount,
                'success_count' => $successCount,
                'validation_errors' => count($validationErrors),
            ]);

        } catch (Throwable $e) {
            Log::error('Oblast data refresh failed', [
                'job_id' => $this->refreshJobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $refreshJobRepository->updateState($this->refreshJobId, RefreshJobStateEnum::FAILED);
            throw $e;
        }
    }
}
