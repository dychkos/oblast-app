<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('has_polygons', function (Blueprint $table) {
            $table->id();
            $table->morphs('polygonable');
            $table->json('geojson');
            $table->timestamps();

            $table->index(['polygonable_id', 'polygonable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('has_polygons');
    }
};
