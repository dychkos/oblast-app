<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\OblastsRefreshRecordRepositoryInterface;
use App\DTOs\CoordinatesDTO;
use App\Http\Requests\CoordinateRequest;
use App\Http\Requests\CreateRefreshOblastsRequest;
use App\Http\Resources\OblastCollectionResource;
use App\Http\Resources\OblastRefreshRecordResource;
use App\Jobs\RefreshOblastDataJob;
use App\Models\OblastsRefreshRecord;
use App\Services\OblastService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OblastController extends ApiController
{
    public function __construct(
        private readonly OblastService $oblastService
    ) {}

    public function index(CoordinateRequest $request): OblastCollectionResource|JsonResponse
    {
        $validated = $request->validated();

        $searchResult = $this->oblastService->findOblastsByCoordinates(
            new CoordinatesDTO($validated['lat'], $validated['lon']),
        );

        return $searchResult->toResource();
    }

    public function createRefreshJob(
        CreateRefreshOblastsRequest $request,
        OblastsRefreshRecordRepositoryInterface $refreshOblastsRepository
    ): JsonResponse {
        $validated = $request->validated();
        $delay = $validated['delay'] ?? 0;

        if ($refreshOblastsRepository->findProcessing()) {
            return $this->errorResponse(
                message: 'The refresh processing is already running.',
                code: Response::HTTP_CONFLICT
            );
        }

        $job = $refreshOblastsRepository->create($delay);

        RefreshOblastDataJob::dispatchSync($job->id)
            ->delay(now()->addSeconds($delay))
            ->onConnection('redis');

        return $this->successResponse([
            'id' => $job->id,
        ]);
    }

    public function getRefreshJobStatus(OblastsRefreshRecord $job): OblastRefreshRecordResource|JsonResponse
    {
        return OblastRefreshRecordResource::make($job);
    }

    public function destroy(): JsonResponse
    {
        $this->oblastService->purgeAllData();

        return $this->successResponse(code: Response::HTTP_NO_CONTENT);
    }
}
