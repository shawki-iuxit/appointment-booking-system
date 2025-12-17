<?php

namespace App\Providers;

use App\Contracts\ClinicRepositoryInterface;
use App\Domains\Clinic\Repositories\ClinicRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ClinicRepositoryInterface::class,
            ClinicRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
