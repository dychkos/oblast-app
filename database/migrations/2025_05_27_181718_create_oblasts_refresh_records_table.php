<?php

use App\Enums\RefreshJobStateEnum;
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
        Schema::create('oblasts_refresh_records', function (Blueprint $table) {
            $table->id();
            $table->string('state')->default(RefreshJobStateEnum::PENDING->value);
            $table->text('error_text')->nullable();
            $table->timestamp('create_ts')->nullable();
            $table->timestamp('delay_ts')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oblasts_refresh_records');
    }
};
