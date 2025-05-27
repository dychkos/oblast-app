<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oblasts', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('display_name');

            $table->decimal('lat', 10, 8);
            $table->decimal('lon', 11, 8);

            $table->string('provider_name');
            $table->string('provider_id');

            $table->timestamps();

            $table->index(['lat', 'lon']);
            $table->index(['provider_name', 'provider_id']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oblasts');
    }
};
