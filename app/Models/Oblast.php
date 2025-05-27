<?php

namespace App\Models;

use App\Traits\HasPolygon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Oblast extends Model
{
    use HasFactory;
    use HasPolygon;

    protected $table = 'oblasts';

    protected $guarded = [];
}
