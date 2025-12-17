<?php

namespace App\Providers;

use App\Contracts\ClinicRepositoryInterface;
use App\Contracts\DoctorRepositoryInterface;
use App\Domains\Clinic\Repositories\ClinicRepository;
use App\Domains\Doctor\Repositories\DoctorRepository;
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

        $this->app->bind(
            DoctorRepositoryInterface::class,
            DoctorRepository::class
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
