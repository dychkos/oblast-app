<?php

use App\Http\Controllers\OblastController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return response()->json([
        'app' => config('app.name'),
    ]);
})->middleware();

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::prefix('oblasts')->group(function () {
    Route::get('/', [OblastController::class, 'index']);
    Route::delete('/', [OblastController::class, 'destroy']);

    Route::prefix('refresh-jobs')->group(function () {
        Route::post('/', [OblastController::class, 'createRefreshJob']);
        Route::get('/{job}', [OblastController::class, 'getRefreshJobStatus']);
    });
});
