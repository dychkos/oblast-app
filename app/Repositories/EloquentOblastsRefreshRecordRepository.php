<?php

namespace App\Repositories;

use App\Contracts\Repositories\OblastsRefreshRecordRepositoryInterface;
use App\Enums\RefreshJobStateEnum;
use App\Models\OblastsRefreshRecord;

class EloquentOblastsRefreshRecordRepository implements OblastsRefreshRecordRepositoryInterface
{
    public function create(int $delay = 0): OblastsRefreshRecord
    {
        return OblastsRefreshRecord::create([
            'create_ts' => now(),
            'delay_ts' => now()->addSeconds($delay),
        ]);
    }

    public function findById(int $id): ?OblastsRefreshRecord
    {
        return OblastsRefreshRecord::find($id);
    }

    public function updateState(int $id, RefreshJobStateEnum $state): void
    {
        OblastsRefreshRecord::where('id', $id)->update(['state' => $state]);
    }

    public function findProcessing(): ?OblastsRefreshRecord
    {
        return OblastsRefreshRecord::where('state', RefreshJobStateEnum::PROCESSING)->first();
    }
}
