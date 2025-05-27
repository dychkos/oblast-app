<?php

namespace App\Providers;

use App\Contracts\OblastsApiProvider;
use App\Contracts\Repositories\OblastRepositoryInterface;
use App\Contracts\Repositories\OblastsRefreshRecordRepositoryInterface;
use App\Models\Oblast;
use App\Repositories\EloquentOblastRepository;
use App\Repositories\EloquentOblastsRefreshRecordRepository;
use App\Services\NominatimApiService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OblastRepositoryInterface::class, function () {
            return new EloquentOblastRepository;
        });

        $this->app->singleton(OblastsRefreshRecordRepositoryInterface::class, function () {
            return new EloquentOblastsRefreshRecordRepository;
        });

        $this->app->singleton(OblastsApiProvider::class, function () {
            return new NominatimApiService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'oblast' => Oblast::class,
        ]);
    }
}
