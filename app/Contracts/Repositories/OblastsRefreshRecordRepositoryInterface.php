<?php

namespace App\Contracts\Repositories;

use App\Enums\RefreshJobStateEnum;
use App\Models\OblastsRefreshRecord;

interface OblastsRefreshRecordRepositoryInterface
{
    public function create(int $delay = 0): OblastsRefreshRecord;

    public function findById(int $id): ?OblastsRefreshRecord;

    public function findProcessing(): ?OblastsRefreshRecord;

    public function updateState(int $id, RefreshJobStateEnum $state): void;
}
