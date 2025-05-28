<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Polygon extends Model
{
    use HasFactory;

    protected $table = 'has_polygons';

    protected $guarded = [];

    public function polygonable(): MorphTo
    {
        return $this->morphTo();
    }
}
