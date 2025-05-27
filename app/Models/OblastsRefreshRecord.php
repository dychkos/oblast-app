<?php

namespace App\Models;

use App\Enums\RefreshJobStateEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OblastsRefreshRecord extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'state',
        'error_text',
        'create_ts',
        'delay_ts',
    ];

    protected $casts = [
        'state' => RefreshJobStateEnum::class,
        'create_ts' => 'datetime',
        'delay_ts' => 'datetime',
    ];
}
