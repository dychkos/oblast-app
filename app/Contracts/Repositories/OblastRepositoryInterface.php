<?php

namespace App\Contracts\Repositories;

use App\DTOs\CoordinatesDTO;
use App\Models\Oblast;
use Illuminate\Database\Eloquent\Collection;

interface OblastRepositoryInterface
{
    public function findAllByCoordinates(CoordinatesDTO $coordinates): Collection;

    public function findByCoordinates(CoordinatesDTO $coordinates): ?Oblast;

    public function create(array $data): Oblast;

    public function truncate(): void;

    public function bulkUpsert(array $oblasts): void;
}
