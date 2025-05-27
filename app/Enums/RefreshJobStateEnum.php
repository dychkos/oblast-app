<?php

namespace App\Enums;

enum RefreshJobStateEnum: string
{
    case PENDING = 'queued';
    case PROCESSING = 'processing';
    case DONE = 'done';
    case FAILED = 'failed';
}
