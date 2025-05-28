<?php

namespace App\Enums;

enum RefreshJobStateEnum: string
{
    case PENDING = 'queued';
    case PROCESSING = 'processing';
    case DONE = 'done';
    case FAILED = 'failed';

    public static function values(): array
    {
        return collect(self::cases())->map(function ($case) {
            return $case->value;
        })->toArray();
    }
}
