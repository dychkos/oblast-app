<?php

namespace App\Models;

use App\Traits\HasPolygon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oblast extends Model
{
    use HasFactory;
    use HasPolygon;

    protected $table = 'oblasts';

    protected $guarded = [];
}
